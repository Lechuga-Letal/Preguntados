<?php
require_once __DIR__ . '/../helper/MailService.php';
 class UsuarioModel{

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

     public function createUser($nombre_completo, $anio_nacimiento, $sexo, $pais, $ciudad, $usuario, $mail, $password, $foto_perfil, $rol = 'Jugador')
     {
         $hashed = password_hash($password, PASSWORD_DEFAULT);

         $sql = "INSERT INTO usuarios 
        (nombre_completo, anio_nacimiento, sexo, pais, ciudad, usuario, mail, password, foto_perfil, rol) 
        VALUES ('$nombre_completo', '$anio_nacimiento', '$sexo', '$pais', '$ciudad', '$usuario', '$mail', '$hashed', '$foto_perfil', '$rol')";

         $success = $this->conexion->query($sql);

         $mailService = new MailService();
         $mailService->enviarBienvenida($usuario, $mail);

         return $success;
     }

     public function getUserWith($user, $password)
     {
         $sql = "SELECT usuario, password, rol FROM usuarios WHERE usuario = '$user'";

         $result = $this->conexion->query($sql);


         if (!$result || count($result) === 0) {
             return [];
         }

         $userRow = $result[0];

         if (password_verify($password, $userRow['password'])) {
             return [$userRow];
         }

         return [];
     }
 }