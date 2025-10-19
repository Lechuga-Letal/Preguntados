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
        // Get user by username
        $sql = "SELECT * FROM usuarios WHERE usuario = '$user'";
        $result = $this->conexion->query($sql);

        if (!$result || count($result) === 0) {
            return [];
        }

        // Assuming $result[0] is the user row
        $userRow = $result[0];

        // Verify hashed password
        if (password_verify($password, $userRow['password'])) {
            return [$userRow]; // login successful
        }

        return []; // password incorrect
    }

}