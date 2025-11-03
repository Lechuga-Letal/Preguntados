<?php
require_once __DIR__ . '/../helper/MailService.php';
 class UsuarioModel{

    private $conexion; 

     public function __construct($conexion)
     {
         $this->conexion = $conexion;
     }

     public function userExists($usuario, $mail)
     {
         $sql = "SELECT id FROM usuarios WHERE usuario = '$usuario' OR mail = '$mail'";
         $result = $this->conexion->query($sql);

         return !empty($result) && count($result) > 0;
     }

     public function createUser($nombre_completo, $anio_nacimiento, $sexo, $pais, $ciudad, $coordenadas, $usuario, $mail, $pass1, $foto_perfil, $rol = 'Jugador')
     {
         $hashed = password_hash($pass1, PASSWORD_DEFAULT);

         $sql = "INSERT INTO usuarios 
        (nombre_completo, anio_nacimiento, sexo, pais, ciudad, coordenadas, usuario, mail, password, foto_perfil, rol) 
        VALUES ('$nombre_completo', '$anio_nacimiento', '$sexo', '$pais', '$ciudad', '$coordenadas', '$usuario', '$mail', '$hashed', '$foto_perfil', '$rol')";

         $success = $this->conexion->query($sql);

         $mailService = new MailService();
         $mailService->enviarBienvenida($usuario, $mail);

         return $success;
     }

     public function getUserWith($user, $password)
     {
         $sql = "SELECT id, usuario, password, rol FROM usuarios WHERE usuario = '$user'";

         $result = $this->conexion->query($sql);


         if (!$result || count($result) === 0) {
             return [];
         }

         $userRow = $result[0];

         if (password_verify($password, $userRow['password'])) {
             return [
                 'id' => $userRow['id'],
                 'usuario' => $userRow['usuario'],
                 'rol' => $userRow['rol']
             ];
         }

         return [];
     }

    public function getUsuarioById($id)
    {
        if (!is_numeric($id)) {
            return null;
        }

        $sql = "SELECT * FROM usuarios WHERE id = $id";
        $resultado = $this->conexion->query($sql);
        //var_dump($resultado);
        if ($resultado && count($resultado) > 0) {
            return (array) $resultado[0];
        }

        return null;
    }

    public function getAllUsuarios()
    {
        $sql = "SELECT * FROM usuarios WHERE rol <> 'Administrador'";
        $resultado = $this->conexion->query($sql);

        if ($resultado && count($resultado) > 0) {
            return $resultado;
        }
        return [];
    }

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

    /*
    public function getUsuarioByNombreUsuario($nombre)
    {
    if ($nombre === null || $nombre === '') {
        return null;
    }

    $sql = "SELECT * FROM usuarios WHERE usuario = '$nombre' LIMIT 1";
    $resultado = $this->conexion->query($sql);

    if (is_array($resultado) && count($resultado) > 0) {
        return $resultado[0];
    }

    if (is_object($resultado) && property_exists($resultado, 'num_rows')) {
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
    }

    return null;
    } */
 }