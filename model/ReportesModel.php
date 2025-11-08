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
        $id_pregunta = (int) $id_pregunta;
        $id_usuario = (int) $id_usuario;
        $descripcion = $this->conexion->real_escape_string($descripcion);

        $sql = "INSERT INTO reporte (id_pregunta, id_usuario, descripcion)
                VALUES ($id_pregunta, $id_usuario, '$descripcion')";

        $result = $this->conexion->query($sql);

        if ($result) {
            $res = $this->conexion->query("SELECT LAST_INSERT_ID() AS id_reporte");
            $row = $res->fetch_assoc();
            return $row['id_reporte'] ?? null;
        }

        return null;
    }



}
?>