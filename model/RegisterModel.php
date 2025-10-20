<?php

class RegisterModel
{
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

    public function createUser($nombre_completo, $anio_nacimiento, $sexo, $pais, $ciudad, $usuario, $mail, $password, $foto_perfil){
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre_completo, anio_nacimiento, sexo, pais, ciudad, usuario, mail, password, foto_perfil) 
            VALUES ('$nombre_completo', '$anio_nacimiento', '$sexo', '$pais', '$ciudad', '$usuario', '$mail', '$hashed', '$foto_perfil')";

        $this->conexion->query($sql);
    }
}

