<?php
class PartidaModel {
    private $conexion;
    private $usuarioModel;

    public function __construct($conexion, $usuarioModel) {
        $this->conexion = $conexion;
        $this->usuarioModel = $usuarioModel;
    }

    public function actualizarNivelJugador($idUsuario,$turno) {
        $partidasMinimas=$this->elJugadorCumpleConLasPartidasMinimas($idUsuario,$turno);
        //Despues validar que tengan un min de preguntas hechas

        if($partidasMinimas){
        //Actualizar por categoria
        $this->actualizarNivelJugadorPorCategoria($turno, $idUsuario);
        }
        //Actualizar general
        $this->actualizarNivelJugadorGeneral($idUsuario);
    }

    public function actualizarNivelJugadorGeneral($idUsuario){

        $nivel=$this->obtenerPromedioDeNivelJugador($idUsuario);

        $update=" UPDATE nivelJugadorGeneral 
                   SET nivel='$nivel'
                   where id_usuario='$idUsuario'";
        $this->conexion->query($update);
    }

    public function obtenerPromedioDeNivelJugador($idUsuario){
        $querySuma=" select sum(nivel) as sumaTotal 
                from nivelJugadorPorCategoria
                where id_usuario='$idUsuario'";
        $resultado=$this->conexion->query($querySuma);

        $nivel=$resultado[0]["sumaTotal"]/5;
        $redondeado = ceil($nivel * 10) / 10;

        return $redondeado;
    }
    public function elJugadorCumpleConLasPartidasMinimas($idUsuario,$turno){
        $idCategoria = $this->obtenerIdCategoriaPorTurno($turno);
        $cantidadDePreguntasHechas = $this->obtenerCantidadTotalDeUsuarioPorCategoria($idUsuario, $idCategoria);

        return ($cantidadDePreguntasHechas>=3);
    }
    public function actualizarNivelJugadorPorCategoria($turno, $idUsuario){
        $idCategoria = $this->obtenerIdCategoriaPorTurno($turno);

        $cantidadDePreguntasBien = $this->obtenerCantidadTotalDeRespondidasCorrectamentePorCategoria($idUsuario, $idCategoria);
        $cantidadDePreguntasHechas = $this->obtenerCantidadTotalDeUsuarioPorCategoria($idUsuario, $idCategoria);

        $ratio = $cantidadDePreguntasBien / $cantidadDePreguntasHechas;
        $nivelUsuario = ceil($ratio * 10) / 10;

        if ($nivelUsuario < 0.1) {
            $nivelUsuario = 0.1;
        }

        $update=" UPDATE nivelJugadorPorCategoria 
                   SET nivel=$nivelUsuario
                   where id_usuario='$idUsuario'
                   and  id_categoria='$idCategoria'";
        $this->conexion->query($update);
    }
    public function obtenerIdCategoriaPorTurno($turno) {
        $query="select id_Categoria as id from turno 
                where id='$turno'";
        $resultado=$this->conexion->query($query);
        return $resultado[0]["id"];
    }
    public function obtenerCantidadTotalDeUsuarioPorCategoria($idUsuario,$idCategoria) {
        $query="select count(*) as correctasTotales 
                from turno 
                where id_usuario='$idUsuario' 
                  and id_categoria='$idCategoria'";
        $resultado=$this->conexion->query($query);

        return $resultado[0]["correctasTotales"];
    }

    public function obtenerCantidadTotalDeRespondidasCorrectamentePorCategoria($idUsuario,$idCategoria) {
        $query="select count(*) as correctasTotales 
                from turno 
                where id_usuario='$idUsuario' 
                  and id_categoria='$idCategoria'
                  and aciertos = 1";
        $resultado=$this->conexion->query($query);

        return $resultado[0]["correctasTotales"];
    }

    public function crearPartida($idUsuario) {
//        $idUsuario = is_numeric($usuario) ? $usuario : $this->usuarioModel->obtenerIdUsuarioPorNombre($usuario);
//        if ($oponente !== null) {
//            $idOponente = is_numeric($oponente) ? $oponente : $this->usuarioModel->obtenerIdUsuarioPorNombre($oponente);
//        }
//        $idOponenteSql = (!empty($idOponente) && $idOponente > 0) ? $idOponente : "NULL";
//        $sql = "INSERT INTO partidas (id_usuario, id_oponente) VALUES ($idUsuario, $idOponenteSql)";
        $sql = "INSERT INTO partidas (id_usuario) VALUES ($idUsuario)";
        $this->conexion->query($sql);

        $idPartidaQuery = "SELECT MAX(id) as id FROM partidas WHERE id_usuario = $idUsuario";
        $resultado = $this->conexion->query($idPartidaQuery);
        if (!empty($resultado) && isset($resultado[0]['id'])) {
            return $resultado[0]['id'] ?? null;
        }
        return null;
    }

    public function obtenerDesafiosPorEstado($idUsuario, $estado) {
        if (!is_numeric($idUsuario) || !in_array($estado, ['finalizada', 'en curso'])) {
            return [];
        }
        $query = " 
            SELECT p.*, CASE WHEN p.id_usuario = $idUsuario THEN u_oponente.usuario ELSE u_usuario.usuario END AS nombre_oponente 
            FROM partidas p 
            JOIN usuarios u_usuario ON u_usuario.id = p.id_usuario 
            JOIN usuarios u_oponente ON u_oponente.id = p.id_oponente 
            WHERE ($idUsuario = p.id_usuario OR $idUsuario = p.id_oponente) 
            AND p.estado = '$estado'";
        $resultado = $this->conexion->query($query);
        if ($resultado && $resultado->num_rows > 0) {
            return $resultado;
        }
        return [];
    }

    public function obtenerPartidasFinalizadasPorId($idUsuario) {
        $query = "SELECT u.usuario as nombreUsuario ,u.pais as paisUsuario ,
                  p.puntaje as puntaje ,p.id as idPartida,
                  DATE_FORMAT(p.fecha_fin, '%d/%m/%Y') as fecha
                  FROM partidas p join usuarios u on p.id_usuario=u.id 
                  WHERE estado='finalizada'
                  AND p.id_usuario = $idUsuario;";

        $result= $this->conexion->query($query);

        if($result && count($result) > 0){
            return $result;
        }

        return null;
    }
    public function obtenerDataDePartidasPorEstado($idUsuario, $estado) {
        $desafios = $this->obtenerDesafiosPorEstado($idUsuario, $estado);
        $data = [];
        foreach ($desafios as $desafio) {
            $idDesafiante = $desafio['id_oponente'] ?? $desafio['id_usuario'];
            $desafiador = $this->usuarioModel->getUsuarioById($idDesafiante);
            if ($desafiador) {
                $data[] = $desafiador;
            }
        }
        return $data;
    }

    public function finalizarPartida($idPartida, $puntajeFinal) {
        $puntajeFinal1 = intval($puntajeFinal);
        $sql = "UPDATE partidas SET fecha_fin = NOW(), puntaje = $puntajeFinal1, estado = 'finalizada' WHERE id = $idPartida";
        @$this->conexion->query($sql);
    }
    public function obtenerTotalAciertosPorPartida($idPartida) {
        $idPartida = intval($idPartida);

        $sql = "SELECT SUM(t.aciertos) AS total
                FROM turno t
                WHERE t.id_partida = $idPartida";
        $resultado = $this->conexion->query($sql);

        return $resultado[0]['total'] ?? 0;
    }

    public function crearTurno($idUsuario, $idPartida, $idCategoria) {
        $sqlTurno = "INSERT INTO turno (id_usuario, id_partida, id_categoria) 
                    VALUES ($idUsuario, $idPartida, $idCategoria)";
        $this->conexion->query($sqlTurno);
        $idTurno = $this->obtenerTurnoReciente($idUsuario, $idPartida);

        $pregunta = $this->obtenerPreguntaAleatoriaPorCategoria($idUsuario, $idCategoria);
        if ($pregunta) {
            $idPregunta = $pregunta['id_pregunta'];
            $sqlTurnoPregunta = "INSERT INTO turno_pregunta (id_turno, id_pregunta) 
                                    VALUES ($idTurno, $idPregunta)";
            $this->conexion->query($sqlTurnoPregunta);

            $_SESSION['pregunta_turno'] = $pregunta; //todo Las cosas de peticiones HTTP y session no van en el servicio/modelo
        }

        return $idTurno;
    }

    public function obtenerTurnoReciente($idUsuario, $idPartida) {
        $sql = "SELECT MAX(id) AS id FROM turno WHERE id_usuario = $idUsuario AND id_partida = $idPartida";
        $resultado = $this->conexion->query($sql);
        return $resultado[0]['id'] ?? null;
    }

    //Todo: aca con el ID de usuario, partida se aplica el filtro de dificultad y demas
    public function getIdPregunta($idUsuario, $idCategoria){
        $pregunta = $this->obtenerPreguntaAleatoriaPorCategoria($idUsuario, $idCategoria);
        return $pregunta['id_pregunta'];
    }

    public function getTotalDePreguntas(){
        $QueryCantidadDePreguntas = "SELECT count(*) as preguntasTotales FROM pregunta";
        $resultado = $this->conexion->query($QueryCantidadDePreguntas );
        return $resultado[0]['preguntasTotales'] ?? null;
    }

    public function evaluarRespuesta($opcionElegida, $idTurno) {
        $idCorrecta = $this->obtenerIdDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno);
        return ($idCorrecta == $opcionElegida);
    }

    public function obtenerIdDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno){
        $idTurno = intval($idTurno);
        $sql = "SELECT r.id_respuesta as id
            FROM turno_pregunta tp
            JOIN respuesta r ON tp.id_pregunta = r.id_pregunta
            WHERE tp.id_turno = $idTurno AND r.es_correcta = TRUE
            LIMIT 1";
        $resultado = $this->conexion->query($sql);
        return $resultado[0]["id"] ?? null;
    }

    public function acreditarAcierto($idTurno, $idPregunta) {
        $sql = "UPDATE turno_pregunta
            SET respondida = TRUE, 
                acierto = TRUE
            WHERE id_turno = $idTurno AND id_pregunta = $idPregunta";
        $this->conexion->query($sql);

        $sqlUpdateAciertos = "
            UPDATE turno 
            SET aciertos =1,
                activo=1
             WHERE id = $idTurno";
        $this->conexion->query($sqlUpdateAciertos);
    }

    public function acreditarIntentoFallido($idTurno, $idPregunta) {
        $sql = "UPDATE turno_pregunta
            SET respondida = true
            WHERE id_turno = $idTurno AND id_pregunta = $idPregunta";
        $this->conexion->query($sql);

        $sqlUpdateAciertos = "UPDATE turno SET activo =  1  WHERE id = $idTurno";
        $this->conexion->query($sqlUpdateAciertos);
    }

    public function mostrarCantidadCorrectasPorPartida($idTurno, $idPartida, $idUsuario) {
        $sql = "SELECT COUNT(*) AS cantidad
        FROM turno_pregunta tp
        JOIN turno t ON tp.id_turno = t.id
        WHERE tp.acierto = TRUE
          AND t.id_partida = $idPartida
          AND t.id_usuario = $idUsuario";
        $resultado = $this->conexion->query($sql);
        return $resultado[0]['cantidad'] ?? 0;
    }

    public function obtenerDescripcionDeLaPreguntaPorTurno($idTurno) {
        $idTurno = intval($idTurno);

        $sql = "SELECT p.descripcion
            FROM turno_pregunta tp
            JOIN pregunta p ON tp.id_pregunta = p.id_pregunta
            WHERE tp.id_turno = $idTurno
              AND tp.respondida = FALSE
            ORDER BY tp.id_pregunta
            LIMIT 1";

        $resultado = $this->conexion->query($sql);
        return $resultado[0]['descripcion'] ?? null;
    }

    public function obtenerRespuestasDelTurno($idTurno) {
        $idTurno = intval($idTurno);

        $sql = "SELECT r.id_respuesta, r.descripcion
            FROM turno_pregunta tp
            JOIN respuesta r ON tp.id_pregunta = r.id_pregunta
            WHERE tp.id_turno = $idTurno
            ORDER BY RAND()
            LIMIT 4";

        $resultado = $this->conexion->query($sql);
        return $resultado ?? [];
    }

    public function obtenerDescripcionDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno){
        $idTurno = intval($idTurno);
        $sql = "
        SELECT r.descripcion AS respuestaCorrecta
        FROM turno_pregunta tp
        JOIN respuesta r ON tp.id_pregunta = r.id_pregunta
        WHERE tp.id_turno = $idTurno
          AND r.es_correcta = TRUE
        LIMIT 1
    ";
        $resultado = $this->conexion->query($sql);
        return $resultado[0]['respuestaCorrecta'] ?? null;
    }

    public function obtenerNombreUsuarioPorTurno($idTurno){
        $sql = "SELECT u.usuario as nombreUsuario FROM turno t join usuarios u on t.id_usuario=u.id WHERE t.id = $idTurno";
        $resultado = $this->conexion->query($sql);
        return $resultado[0]["nombreUsuario"] ?? null;
    }

    public function obtenerIdPartidaPorTurno($idTurno){
        $idTurno = intval($idTurno);
        if ($idTurno <= 0) return null;

        $sql = "SELECT p.id as id FROM turno t join partidas p on t.id_partida=p.id WHERE t.id = $idTurno";
        $resultado = $this->conexion->query($sql);
        return $resultado[0]["id"] ?? null;
    }

    public function getNombreOponente($idTurno){
        $sql = "SELECT u.usuario as nombreOponente FROM turno t join partidas p on t.id_partida=p.id join usuarios u on p.id_oponente=u.id WHERE t.id = $idTurno";
        $resultado = $this->conexion->query($sql);

        if ($resultado && count($resultado) > 0) {
            return $resultado[0]["nombreOponente"] ?? null;
        }
        return null;
    }

    //todo Metodo que voy que tocar REVISAR
    public function obtenerPreguntaAleatoriaPorCategoria($idUsuario, $idCategoria) {
        $nivelUsuario= $this->usuarioModel->getNivelUsuarioPorCategoria($idUsuario,$idCategoria);
        $margen= 0.2;

        $sql1 = "SELECT COUNT(*) AS total FROM pregunta WHERE id_categoria = $idCategoria";
        $totalQuery = $this->conexion->query($sql1);
        $total = (int)($totalQuery[0]['total'] ?? 0);


        $vistasQuery = $this->conexion->query("
        SELECT COUNT(*) AS vistas 
        FROM preguntasVistas pv
        JOIN pregunta p ON pv.id_pregunta = p.id_pregunta
        WHERE pv.id_usuario = $idUsuario AND p.id_categoria = $idCategoria
    ");
        $vistas = (int)($vistasQuery[0]['vistas'] ?? 0);


        //Borra las preguntas en caso de que se hayan visto todas
        if ($vistas >= $total && $total > 0) {
            $this->conexion->query("
            DELETE FROM preguntasVistas 
            WHERE id_usuario = $idUsuario
              AND id_pregunta IN (
                  SELECT id_pregunta FROM pregunta WHERE id_categoria = $idCategoria
              )
        ");
            $vistas = 0;
        }

//      se obtiene preguntas de la categoria y que no haya hecho el jugador
        $sql = "
        SELECT p.*
        FROM pregunta p
        WHERE p.id_categoria = $idCategoria
        AND p.dificultad BETWEEN ($nivelUsuario - $margen) AND ($nivelUsuario + $margen)
        AND p.id_pregunta NOT IN (
              SELECT pv.id_pregunta
              FROM preguntasVistas pv
              JOIN pregunta p2 ON pv.id_pregunta = p2.id_pregunta
              WHERE pv.id_usuario = $idUsuario AND p2.id_categoria = $idCategoria
          )
        ORDER BY RAND()
        LIMIT 1
    ";

        $resultado = $this->conexion->query($sql);


        //En caso de que no encuentre una, borra all y vuelve a cargar el resultado
        if (empty($resultado)) {
            var_dump("no encontre preguntas");
            echo "<br>";
//            $this->conexion->query("
//            DELETE FROM preguntasVistas
//            WHERE id_usuario = $idUsuario
//              AND id_pregunta IN (
//                  SELECT id_pregunta FROM pregunta WHERE id_categoria = $idCategoria)");
//            $resultado = $this->conexion->query($sql);
            $resultado = $this->obtenerPreguntaRandomNoHecha($idUsuario, $idCategoria);
        }

        if (empty($resultado)) {
            return null;
        }

        $pregunta = $resultado[0];
        $idPregunta = (int)$pregunta['id_pregunta'];

        $this->conexion->query("
        INSERT IGNORE INTO preguntasVistas (id_usuario, id_pregunta)
        VALUES ($idUsuario, $idPregunta)
    ");

        return $pregunta;
    }
    public function obtenerPreguntaRandomNoHecha($idUsuario, $idCategoria){
        $sql = "
        SELECT p.*
        FROM pregunta p
        WHERE p.id_categoria = $idCategoria
        AND p.id_pregunta NOT IN (
              SELECT pv.id_pregunta
              FROM preguntasVistas pv
              JOIN pregunta p2 ON pv.id_pregunta = p2.id_pregunta
              WHERE pv.id_usuario = $idUsuario AND p2.id_categoria = $idCategoria
          )
        ORDER BY RAND()
        LIMIT 1
    ";
        $resultado = $this->conexion->query($sql);
        return $resultado;
    }

    /*public function finalizarTurno($idTurno){
        $sql = "UPDATE turno SET fin_turno= NOW() WHERE id = $idTurno";
        $this->conexion->query($sql);
    }


    todo esto era para calcular tiempo desde la base de datos
    public function calcularTiempoDeRespuesta($idTurno){
        $sql = "SELECT TIMESTAMPDIFF(SECOND, inicio_turno, fin_turno) as tiempoSegundos FROM turno WHERE id = $idTurno";
        $resultado = $this->conexion->query($sql);
        if ($resultado && count($resultado) > 0) {
            return $resultado[0]["tiempoSegundos"];
        }
        return null;
    }

    public function verificarTiempoDelTurno($idTurno){
        $tiempoLimiteSegundos = 15; //desde aca modificamos el tiempo limite en el backend
        $tiempoRespuesta = $this->calcularTiempoDeRespuesta($idTurno);
        if ($tiempoRespuesta !== null) {
            return $tiempoRespuesta <= $tiempoLimiteSegundos;
        }
        return false; // Si no se pudo calcular el tiempo, consideramos que no está dentro del límite
    }*/


    public function mensajeDeRevisionDeErrores(){
        var_dump("llegue");
        die();
    }
}

