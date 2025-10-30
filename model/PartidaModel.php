<?php
class PartidaModel
{
    private $conexion;
    private $usuarioModel;

    public function __construct($conexion, $usuarioModel)
    {
        $this->conexion = $conexion;
        $this->usuarioModel = $usuarioModel;
    }

    //Capaz esto lo podemos mover al modelo de usuario... 
    public function obtenerIdUsuarioPorNombre($nombreUsuario)
    {
        $sql = "SELECT id FROM usuarios WHERE usuario = '$nombreUsuario'";
        //Agrege el @ para que el warning no aparecza. 
        //Pero deberiamos preguntar por el foro porque a veces nos sale y a veces no!
        $resultado = @$this->conexion->query($sql);

        if ($resultado && count($resultado) > 0) {
            return $resultado[0]['id'];
        }

        return null;
    }

    public function crearPartida($usuario, $oponente)
    {
        $idUsuario = is_numeric($usuario) ? $usuario : $this->obtenerIdUsuarioPorNombre($usuario);

        if ($oponente !== null) {
            $idOponente = is_numeric($oponente) ? $oponente : $this->obtenerIdUsuarioPorNombre($oponente);
        }

        $idOponenteSql = (!empty($idOponente) && $idOponente > 0) ? $idOponente : "NULL";

        $sql = "INSERT INTO partidas (id_usuario, id_oponente) VALUES ($idUsuario, $idOponenteSql)";
        @$this->conexion->query($sql);

        $idPartidaQuery = "SELECT MAX(id) as id FROM partidas WHERE id_usuario = $idUsuario";
        $resultado = $this->conexion->query($idPartidaQuery);

        // Directly check array
        if (!empty($resultado) && isset($resultado[0]['id'])) {
            return $resultado[0]['id'];
        }

        return null;
    }


    public function obtenerListadoDeJugadoresMenos($idActual)
    {

        if (!is_numeric($idActual)) {
            $idUsuario = $this->obtenerIdUsuarioPorNombre($idActual);
        } else {
            $idUsuario = $idActual;
        }

        $query = "SELECT foto_perfil, id, usuario, nombre_completo, pais FROM usuarios 
        WHERE id != $idUsuario AND usuario != 'admin'";

        $resultado = @$this->conexion->query($query);

        if (is_array($resultado)) {
            return $resultado;
        }

        $usuarios = [];
        while ($fila = $resultado->fetch_assoc()) {
            $usuarios[] = $fila;
        }

        return $usuarios;
    }

    public function obtenerDesafiosPorEstado($idUsuario, $estado)
    {
        if (!is_numeric($idUsuario) || !in_array($estado, ['finalizada', 'en curso'])) {
            return [];
        }

        $query = "
            SELECT 
                p.*, 
                CASE 
                    WHEN p.id_usuario = $idUsuario THEN u_oponente.usuario
                    ELSE u_usuario.usuario
                END AS nombre_oponente
            FROM partidas p
            JOIN usuarios u_usuario ON u_usuario.id = p.id_usuario
            JOIN usuarios u_oponente ON u_oponente.id = p.id_oponente
            WHERE ($idUsuario = p.id_usuario OR $idUsuario = p.id_oponente)
            AND p.estado = '$estado'
        ";

        $resultado = $this->conexion->query($query);

        if ($resultado && count($resultado) > 0) {
            return $resultado;
        }

        return [];
    }

    public function obtenerDataDePartidasPorEstado($idUsuario, $estado)
    {
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

    public function finalizarPartida($idPartida, $puntajeFinal)
    {
        //$idPartida = $idPartida;
        $puntajeFinal = intval($puntajeFinal);

        $sql = "UPDATE partidas
            SET fecha_fin = NOW(),
                puntaje = $puntajeFinal,
                estado = 'finalizada'
            WHERE id = $idPartida";

        @$this->conexion->query($sql);
    }

    public function obtenerPregunta()
    {//Por ahora devuelve siempre la misma pregunta y respuestas
        $sql = "SELECT * FROM pregunta where id_categoria = 2";
        $obtencionPregunta = $this->conexion->query($sql);

        $sql_respuestas = "SELECT * FROM respuesta WHERE id_pregunta = 2";
        $obtencionRespuestas = $this->conexion->query($sql_respuestas);

        return [
            'pregunta' => $obtencionPregunta,
            'respuestas' => $obtencionRespuestas
        ];
    }


    //TODO: Probablemente estos metodos lo tengamos que distribuir en varios modelos
    public function crearTurno($nombreUsuario, $idPartida){
        $idUsuario = $this->obtenerIdUsuarioPorNombre($nombreUsuario);
        $idPregunta=$this->getIdPregunta();

        $sql = "INSERT INTO turno (id_partida, id_usuario,id_pregunta) 
                VALUES ($idPartida, $idUsuario,$idPregunta)";
        $this->conexion->query($sql);

        return $this->obtenerTurnoReciente($idUsuario);
    }

    public function obtenerTurnoReciente($idusuario){
        $idPartidaQuery = "SELECT MAX(id) as id FROM turno WHERE id_usuario = $idusuario";
        $resultado = $this->conexion->query($idPartidaQuery);

        return $resultado[0]['id'];
    }

    //Todo: aca con el ID de usuario, partida se aplica el filtro de dificultad y demas
    public function getIdPregunta(){
        $cantidadDePreguntas = $this->getTotalDePreguntas();
        $idPreguntaRandom = rand(1, $cantidadDePreguntas);

        $query= "SELECT id_pregunta as id  
                 FROM pregunta
                 where id_pregunta = $idPreguntaRandom";

        $resultado = $this->conexion->query($query);

        return $resultado[0]['id'];
    }

    public function getTotalDePreguntas(){
        $QueryCantidadDePreguntas = "SELECT count(*) as preguntasTotales FROM pregunta";
        $resultado = $this->conexion->query($QueryCantidadDePreguntas );

        return $resultado[0]['preguntasTotales'];
    }

    public function evaluarRespuestaDelTurno($opcionElegida,$idTurno){
        $idDeRespuestaCorrecta=$this->obtenerIdDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno);

        return ($idDeRespuestaCorrecta==$opcionElegida);
    }
    
    public function acreditarAcierto($idTurno){
        $sql = "UPDATE turno
                    SET adivino=true
                    WHERE id = $idTurno";

        $this->conexion->query($sql);
    }

    public function mostrarCantidadCorrectas($idTurno){
        $idPartida=$this->obtenerIdPartidaPorTurno($idTurno);
        $nombreUsuario=$this->obtenerNombreUsuarioPorTurno($idTurno);
        $idJugador=$this->obtenerIdUsuarioPorNombre($nombreUsuario);

        $sql = "select count(*) as cantidad from turno 
                where id_usuario=$idJugador 
                and id_partida=$idPartida
                and adivino=true;";

        $resultado = $this->conexion->query($sql);

        if ($resultado && count($resultado) > 0) {
            return $resultado[0]["cantidad"];
        }
        return null;
    }

    public function obtenerDescripcionDeLaPreguntaPorTurno($idTurno){
        $sql = "SELECT p.descripcion FROM pregunta p 
                join Turno t on p.id_pregunta=t.id_pregunta 
                WHERE t.id = $idTurno";

        $resultado = $this->conexion->query($sql);

        return $resultado[0]['descripcion'];
    }

    public function obtenerRespuestasDelTurno($idTurno){
        $sql = "SELECT r.id_respuesta as id,r.descripcion as opcion 
                FROM respuesta r 
                join Turno t on r.id_pregunta=t.id_pregunta 
                WHERE t.id = $idTurno";

        $resultado = $this->conexion->query($sql);

        return $resultado;
    }

    public function obtenerIdDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno){
         $sql = "SELECT r.id_respuesta as id, r.descripcion as respuestaCorrecta
                FROM turno t 
                join respuesta r on t.id_pregunta=r.id_pregunta
                WHERE t.id = $idTurno
                and es_correcta=true";

        $resultado = $this->conexion->query($sql);

        return $resultado[0]["id"];
    }

    public function obtenerDescripcionDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno){
        $sql = "SELECT r.id_respuesta as id, r.descripcion as respuestaCorrecta
                FROM turno t 
                join respuesta r on t.id_pregunta=r.id_pregunta
                WHERE t.id = $idTurno
                and es_correcta=true";

        $resultado = $this->conexion->query($sql);

        return $resultado[0]["respuestaCorrecta"];
    }

    public function obtenerNombreUsuarioPorTurno($idTurno){
        $sql = "SELECT u.usuario as nombreUsuario
                FROM turno t 
                join usuarios u on t.id_usuario=u.id
                WHERE t.id = $idTurno";

        $resultado = $this->conexion->query($sql);

        return $resultado[0]["nombreUsuario"];
    }
    public function obtenerIdPartidaPorTurno($idTurno){
        $sql = "SELECT p.id as id
                FROM turno t 
                join partidas p on t.id_partida=p.id
                WHERE t.id = $idTurno";

        $resultado = $this->conexion->query($sql);

        return $resultado[0]["id"];
    }

    public function getNombreOponente($idTurno){
        $sql = "SELECT u.usuario as nombreOponente 
                FROM turno t 
                join partidas p on t.id_partida=p.id
                join usuarios u on p.id_oponente=u.id
                WHERE t.id = $idTurno";

        $resultado = $this->conexion->query($sql);

        if ($resultado && count($resultado) > 0) {
            return $resultado[0]["nombreOponente"];
        }
        return null;
    }
}


