<?php

class ReportesModel
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;
    }

    public function crearReporte($id_pregunta, $id_usuario, $descripcion)
    {
        $sql = "INSERT INTO reporte (id_pregunta, id_usuario, descripcion)
                VALUES ($id_pregunta, $id_usuario, '$descripcion')";

        $result = $this->conexion->query($sql);
    }

}
?>