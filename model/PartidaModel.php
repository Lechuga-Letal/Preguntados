<?php
class PartidaModel
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    //Capaz esto lo podemos mover al modelo de usuario... 
    private function obtenerIdUsuarioPorNombre($nombreUsuario)
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
        if (!is_numeric($usuario)) {
            $idUsuario = $this->obtenerIdUsuarioPorNombre($usuario);
        } else {
            $idUsuario = $usuario;
        }

        if ($oponente !== null) {
            $idOponente = is_numeric($oponente) ? $oponente : $this->obtenerIdUsuarioPorNombre($oponente);
        }

        $sql = "INSERT INTO partidas (id_usuario, id_oponente) VALUES ($idUsuario, " . ($idOponente ?? "NULL") . ")";
        @$this->conexion->query($sql);

        $idPartida = "SELECT MAX(id) as id FROM partidas WHERE id_usuario = $idUsuario";
        $resultado = @$this->conexion->query($idPartida);

        if ($resultado && count($resultado) > 0) {
            return $resultado[0]['id'];
        }

        return null;
    }

    public function obtenerListadoDeJugadores($idActual) {
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
}


