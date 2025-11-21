<?php

class CategoriasModel
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function getCategoriasYPorcentaje() 
    {
        $query = "SELECT nombre, AVG(nivel) as promedio_nivel FROM categoria
            LEFT OUTER JOIN niveljugadorporcategoria 
            ON categoria.id_categoria = niveljugadorporcategoria.id_categoria
            GROUP BY categoria.id_categoria, nombre";
        $result = $this->conexion->query($query);
        return $result; 
    }

    public function crearNuevaCategoria($nomnre, $imagen) {
        $query = "INSERT INTO categoria (nombre, foto_categoria) VALUES
        ('$imagen','$imagen')"
        $result = $this->conexion->query($query);
    }

}