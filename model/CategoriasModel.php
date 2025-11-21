<?php

class CategoriasModel
{
    private $conexion;

    private $MIN_PREGUNTAS = 5;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function getCategoriasActivasData() 
    {
        $query = "
            SELECT 
                categoria.id_categoria,
                categoria.nombre,
                categoria.estado,
                COUNT(DISTINCT pregunta.id_pregunta) AS cantidad_preguntas,
                AVG(niveljugadorporcategoria.nivel) AS promedio_nivel
            FROM categoria
            LEFT JOIN niveljugadorporcategoria 
                ON categoria.id_categoria = niveljugadorporcategoria.id_categoria
            LEFT JOIN pregunta
                ON categoria.id_categoria = pregunta.id_categoria
            WHERE categoria.estado = 1
            GROUP BY categoria.id_categoria, categoria.nombre, categoria.estado
        ";

        return $this->conexion->query($query);
    }

    public function getCategoriasInactivasData()
    {
        $query = "
            SELECT 
                c.id_categoria,
                c.nombre,
                c.estado,
                COALESCE(p.cantidad_preguntas, 0) AS cantidad_preguntas
            FROM categoria c
            LEFT JOIN (
                SELECT id_categoria, COUNT(*) AS cantidad_preguntas
                FROM pregunta
                GROUP BY id_categoria
            ) p ON c.id_categoria = p.id_categoria
            WHERE c.estado = 0
            ORDER BY c.nombre
        ";
        return $this->conexion->query($query);
    }

    public function getCategoriasActivas()
    {
        $query = "SELECT * FROM categoria WHERE estado = 1";
        $result = $this->conexion->query($query);
        return $result; 
    }

    public function getAllCategorias() 
    {
        $query = "SELECT * FROM categoria";
        $result = $this->conexion->query($query);
        return $result; 
    }

    public function crearNuevaCategoria($nombre, $imagen)
    {
        if ($imagen !== null && strlen($imagen) > 50) {
            return false; 
        }

        $query = "
            INSERT INTO categoria (nombre, foto_categoria, estado)
            VALUES ('$nombre', '$imagen', 0)
        ";

        return $this->conexion->query($query);
    }

public function actualizarCategoria($id_categoria)
{
    $MIN_PREGUNTAS = $this->MIN_PREGUNTAS;

    $queryCount = "
        SELECT COUNT(*) AS cantidad 
        FROM pregunta 
        WHERE id_categoria = $id_categoria
    ";

    $result = $this->conexion->query($queryCount);

    $cantidad = ($result && isset($result[0]['cantidad']))
        ? (int)$result[0]['cantidad']
        : 0;

    $nuevoEstado = ($cantidad >= $MIN_PREGUNTAS) ? 1 : 0;

    $queryUpdate = "
        UPDATE categoria 
        SET estado = $nuevoEstado
        WHERE id_categoria = $id_categoria
    ";

    return $this->conexion->query($queryUpdate);
}



    public function getMinPreguntas()
    {
        return $this->MIN_PREGUNTAS; 
    }

}