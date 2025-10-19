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
        // Same style as LoginModel
        $sql = "SELECT id FROM usuarios WHERE usuario = '$usuario' OR mail = '$mail'";
        $result = $this->conexion->query($sql);

        // Check if any row exists
        return !empty($result) && count($result) > 0;
    }

    public function createUser($usuario, $mail, $password)
    {
        // Hash password
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (usuario, mail, password) VALUES ('$usuario', '$mail', '$hashed')";
        $this->conexion->query($sql);
    }
}

