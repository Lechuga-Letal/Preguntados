<?php

class InicioModel
{

    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function logout()
    {
        session_start(); 
        $_SESSION = []; 
        session_destroy(); 
        header("Location: /Preguntados/login"); 
        exit;
    }

    public function goToPaginaPrincipal()
    {
        header("Location: /Preguntados/login");
        exit;
    }
}