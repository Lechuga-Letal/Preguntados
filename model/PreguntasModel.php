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

    public function actualizarNivel($idPregunta){
        $cantidadDeVecesDada=$this->obtenerCantidadDeVecesDada($idPregunta);

        if($cantidadDeVecesDada>=2){
            $cantidadDeVecesRespondidasCorrectamente=$this->obtenerCantidadDeRespondidasCorrectamente($idPregunta);

            $ratio = $cantidadDeVecesRespondidasCorrectamente / $cantidadDeVecesDada;
            $dificultadPregunta = ceil($ratio * 10) / 10;

            if ($dificultadPregunta < 0.1) {
                $dificultadPregunta = 0.1;
            }

            $update=" UPDATE pregunta 
                   SET dificultad=$dificultadPregunta
                   where id_pregunta='$idPregunta'";
            $this->conexion->query($update);
        }
    }
    public function obtenerCantidadDeVecesDada($idPregunta){
        //poner condicion que minino se haya respondido 3 veces
        $sql="select cant_de_veces_respondidas as dadas from pregunta
              where id_pregunta=$idPregunta";

        $resultado=$this->conexion->query($sql);
        return $resultado[0]['dadas'];
    }
    public function obtenerCantidadDeRespondidasCorrectamente($idPregunta){
        //poner condicion que minino se haya respondido 3 veces
        $sql="select cant_de_veces_respondidas_correctamente as correctas from pregunta
              where id_pregunta=$idPregunta";

        $resultado=$this->conexion->query($sql);
        return $resultado[0]['correctas'];
    }
    public function actualizarCantidades($idPregunta, $acierto){

        if($acierto){
            $this->actualizarCantidadRespondidasCorrectamente($idPregunta);
        }
        $this->actualizarCantidadRespondidas($idPregunta);
    }
   public function actualizarCantidadRespondidasCorrectamente($idPregunta){
        $sql = "update pregunta 
                set cant_de_veces_respondidas_correctamente=cant_de_veces_respondidas_correctamente+1
                where id_pregunta=$idPregunta";
        $this->conexion->query($sql);
    }
    public function actualizarCantidadRespondidas($idPregunta){
        $sql = "update pregunta 
                set cant_de_veces_respondidas=cant_de_veces_respondidas+1
                where id_pregunta=$idPregunta";
        $this->conexion->query($sql);
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
        $sql = "
            SELECT 
                p.*, 
                c.nombre AS categoria
            FROM pregunta p
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            WHERE p.id_pregunta = $id
        ";
        $result = $this->conexion->query($sql);
        return $result[0] ?? null;
    }

    public function obtenerSugerenciaPorId($id)
    {
        $sql = "
            SELECT 
                s.*, 
                c.nombre AS categoria
            FROM sugerencia s
            LEFT JOIN categoria c ON s.id_categoria = c.id_categoria
            WHERE s.id_sugerencia = $id
        ";
        $result = $this->conexion->query($sql);
        return $result[0] ?? null;
    }

    public function obtenerPreguntasConRespuestas()
    {
        $sql = "
            SELECT 
                p.id_pregunta AS pregunta_id, 
                p.descripcion AS pregunta, 
                r.descripcion AS respuesta, 
                r.es_correcta,
                c.nombre AS categoria
            FROM pregunta p
            LEFT JOIN respuesta r ON p.id_pregunta = r.id_pregunta
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
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
                c.nombre AS categoria,
                rep_counts.cantidad_reportes,
                r.descripcion AS respuesta,
                r.es_correcta
            FROM pregunta p
            LEFT JOIN respuesta r ON p.id_pregunta = r.id_pregunta
            LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
            INNER JOIN (
                SELECT id_pregunta, COUNT(*) AS cantidad_reportes
                FROM reporte
                GROUP BY id_pregunta
            ) AS rep_counts ON p.id_pregunta = rep_counts.id_pregunta
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
                rs.es_correcta,
                u.usuario AS usuario_sugirio,
                c.nombre AS categoria
            FROM sugerencia s
            LEFT JOIN respuesta_sugerida rs ON s.id_sugerencia = rs.id_sugerencia
            INNER JOIN usuarios u ON s.id_usuario = u.id
            LEFT JOIN categoria c ON s.id_categoria = c.id_categoria
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

    public function obtenerPreguntasDeLaUltimaPartidaDelJugador($idUsuario){
        $idPartida=$this->obtenerIdDeUltimaPartida($idUsuario);

        $sql="
            SELECT p.id_pregunta as 'id' ,p.descripcion as 'pregunta' FROM turno t 
            join turno_pregunta tp on t.id=tp.id_turno
            join pregunta p on p.id_pregunta=tp.id_pregunta
            where t.id_partida=$idPartida";
        $result = $this->conexion->query($sql);

        return $result;
    }

    public function obtenerIdDeUltimaPartida($idUsuario){
        $sql="select id from partidas
                where id_usuario = $idUsuario
                order by id desc 
                limit 1";
        $result=$this->conexion->query($sql);

        return $result[0]['id'];
    }

    public function getCategoriaDe($id_pregunta)
    {
        $query = "SELECT id_categoria FROM pregunta WHERE id_pregunta = $id_pregunta";
        $result = $this->conexion->query($query);

        return ($result && isset($result[0]['id_categoria']))
            ? $result[0]['id_categoria']
            : null;
    }
}