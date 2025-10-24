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

    public function obtenerPreguntasReportadas()
    {
        $sql = "
            SELECT 
                p.id_pregunta AS pregunta_id,
                p.descripcion AS pregunta,
                r.descripcion AS respuesta,
                r.es_correcta
            FROM pregunta p
            LEFT JOIN respuesta r ON p.id_pregunta = r.id_pregunta
            WHERE p.id_pregunta IN (SELECT id_pregunta FROM reporte)
            ORDER BY p.id_pregunta;
        ";
        return $this->conexion->query($sql);
    }

    public function obtenerPreguntasSugeridas()
    {
        $sql = "
            SELECT 
                s.id_sugerencia AS pregunta_id,
                s.descripcion AS pregunta,
                rs.descripcion AS respuesta,
                rs.es_correcta
            FROM sugerencia s
            LEFT JOIN respuesta_sugerida rs ON s.id_sugerencia = rs.id_sugerencia
            ORDER BY s.id_sugerencia;
        ";
        return $this->conexion->query($sql);
    }
}