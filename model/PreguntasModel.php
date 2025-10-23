<?php

class PreguntasModel
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function insertarPregunta($descripcion, $id_categoria)
    {
        $sql = "INSERT INTO pregunta (descripcion, id_categoria) VALUES ('$descripcion', $id_categoria)";
        @$this->conexion->query($sql);

        $res = $this->conexion->query("SELECT LAST_INSERT_ID() AS id");
        return $res[0]['id'] ?? null;
    }

    public function obtenerPreguntasConRespuestas()
    {
        $sql = "
            SELECT 
                p.id_pregunta AS pregunta_id, 
                p.descripcion AS pregunta, 
                r.descripcion AS respuesta, 
                r.es_correcta
            FROM pregunta p
            LEFT JOIN respuesta r ON p.id_pregunta = r.id_pregunta
            ORDER BY p.id_pregunta;
        ";

        return $this->conexion->query($sql);
    }

}