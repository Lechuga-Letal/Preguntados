<?php

class LoginModel
{

    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function getUserWith($user, $password)
    {
        $sql = "SELECT * FROM usuarios WHERE usuario = '$user'";
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