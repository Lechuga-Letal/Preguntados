<?php

class InicioModel
{

    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }
}