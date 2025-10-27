<?php

class RespuestasModel
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function insertarRespuesta($descripcion, $es_correcta, $id_pregunta)
    {
        $sql = "INSERT INTO respuesta (descripcion, es_correcta, id_pregunta) 
                VALUES ('$descripcion', $es_correcta, $id_pregunta)";
        @$this->conexion->query($sql);
    }

    public function obtenerRespuestasPorPregunta($id_pregunta)
    {
        $sql = "SELECT * FROM respuesta WHERE id_pregunta = $id_pregunta";
        return $this->conexion->query($sql);
    }

    public function obtenerRespuestasSugeridas($id_sugerencia)
    {
        $sql = "SELECT * FROM respuesta_sugerida WHERE id_sugerencia = $id_sugerencia";
        return $this->conexion->query($sql);
    }
}