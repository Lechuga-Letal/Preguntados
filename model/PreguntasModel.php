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

    public function insertarPreguntaSugerida($descripcion, $id_categoria, $id_usuario)
    {
        $sql = "INSERT INTO sugerencia (descripcion, id_categoria, id_usuario, estado)
                VALUES ('$descripcion', $id_categoria, $id_usuario, 'pendiente')";
        @$this->conexion->query($sql);

        $res = $this->conexion->query("SELECT LAST_INSERT_ID() AS id_sugerencia");
        return $res[0]['id_sugerencia'] ?? null;
    }

    public function obtenerPreguntaPorId($id)
    {
        $sql = "SELECT * FROM pregunta WHERE id_pregunta = $id";
        $result = $this->conexion->query($sql);
        return $result[0] ?? null;
    }

    public function obtenerSugerenciaPorId($id)
    {
        $sql = "SELECT * FROM sugerencia WHERE id_sugerencia = $id";
        $result = $this->conexion->query($sql);
        return $result[0] ?? null;
    }

    //Activas, incluye reportadas 
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

    //Devuelve preguntas con al menos un reporte, y su cantidad
    //a cambiar a reportes model
    public function obtenerPreguntasReportadas()
    {
        $sql = "
            SELECT 
                p.id_pregunta AS pregunta_id,
                p.descripcion AS pregunta,
                rep_counts.cantidad_reportes,
                r.descripcion AS respuesta,
                r.es_correcta
            FROM pregunta p
            LEFT JOIN respuesta r ON p.id_pregunta = r.id_pregunta
            INNER JOIN (
                SELECT id_pregunta, COUNT(*) AS cantidad_reportes
                FROM reporte
                GROUP BY id_pregunta
            ) AS rep_counts ON p.id_pregunta = rep_counts.id_pregunta
            ORDER BY p.id_pregunta;
        ";
        return $this->conexion->query($sql);
    } 

    //Estaria bueno mover esto despues
    public function obtenerReportesPorPregunta($id_pregunta)
    {
        $id_pregunta = (int)$id_pregunta;

        $sql = "
            SELECT u.usuario, r.descripcion
            FROM reporte r
            JOIN usuarios u ON r.id_usuario = u.id
            WHERE r.id_pregunta = $id_pregunta
        ";

        $reportes = $this->conexion->query($sql);

        if (empty($reportes)) {
            return null; // no reports
        }

        return [
            'cantidad' => count($reportes),
            'reportes' => $reportes
        ];
    }

        public function rechazarReportes($id_pregunta) 
    {
        $id_pregunta = (int)$id_pregunta;
        if ($id_pregunta > 0) {
            $sql = "DELETE FROM reporte WHERE id_pregunta = $id_pregunta";
            $this->conexion->query($sql);
        }
    }

    public function obtenerPreguntasSugeridas()
    {
        $sql = "
            SELECT 
                s.id_sugerencia AS pregunta_id,
                s.descripcion AS pregunta,
                rs.descripcion AS respuesta,
                rs.es_correcta,
                u.usuario AS usuario_sugirio
            FROM sugerencia s
            LEFT JOIN respuesta_sugerida rs ON s.id_sugerencia = rs.id_sugerencia
            INNER JOIN usuarios u ON s.id_usuario = u.id
            ORDER BY s.id_sugerencia;
        ";
        return $this->conexion->query($sql);
    }

    public function eliminarPregunta($id_pregunta)
    {
        $id_pregunta = (int)$id_pregunta;
        if ($id_pregunta > 0) {
            $sql = "DELETE FROM pregunta WHERE id_pregunta = $id_pregunta";
            $this->conexion->query($sql);
        }
    }

    public function darDeAltaASugerencia($id_sugerencia)
    {
        $id_sugerencia = (int)$id_sugerencia;
        if ($id_sugerencia <= 0) return false;

        // 1. Obtener la sugerencia
        $sugerencia = $this->obtenerSugerenciaPorId($id_sugerencia);
        if (!$sugerencia) return false;

        // 2. Insertar la pregunta activa
        $id_pregunta = $this->insertarPreguntaDesdeSugerencia($sugerencia);
        if (!$id_pregunta) return false;

        // 3. Mover las respuestas sugeridas a respuestas activas
        $this->moverRespuestasSugeridas($id_sugerencia, $id_pregunta);

        // 4. Eliminar la sugerencia original y sus respuestas
        $this->eliminarSugerenciaCompleta($id_sugerencia);

        return $id_pregunta;
    }

    private function insertarPreguntaDesdeSugerencia($sugerencia)
    {
        $descripcion = addslashes($sugerencia['descripcion']);
        $id_categoria = (int)$sugerencia['id_categoria'];

        $sql = "INSERT INTO pregunta (descripcion, id_categoria)
                VALUES ('$descripcion', $id_categoria)";
        $this->conexion->query($sql);

        // Obtener ID recién insertado
        $sql_get_id = "SELECT id_pregunta FROM pregunta
                    WHERE descripcion = '$descripcion' AND id_categoria = $id_categoria
                    ORDER BY id_pregunta DESC LIMIT 1";
        $result = $this->conexion->query($sql_get_id);

        return !empty($result) ? (int)$result[0]['id_pregunta'] : null;
    }

    private function moverRespuestasSugeridas($id_sugerencia, $id_pregunta)
    {
        $sql = "SELECT descripcion, es_correcta FROM respuesta_sugerida
                WHERE id_sugerencia = $id_sugerencia";
        $respuestas = $this->conexion->query($sql);

        if (empty($respuestas)) return;

        foreach ($respuestas as $r) {
            $desc = addslashes($r['descripcion']);
            $es_correcta = (int)$r['es_correcta'];
            $sql_insert = "INSERT INTO respuesta (descripcion, es_correcta, id_pregunta)
                        VALUES ('$desc', $es_correcta, $id_pregunta)";
            $this->conexion->query($sql_insert);
        }
    }

    public function eliminarSugerenciaCompleta($id_sugerencia)
    {
        $id_sugerencia = (int)$id_sugerencia;
        $this->conexion->query("DELETE FROM respuesta_sugerida WHERE id_sugerencia = $id_sugerencia");
        $this->conexion->query("DELETE FROM sugerencia WHERE id_sugerencia = $id_sugerencia");
    }
}