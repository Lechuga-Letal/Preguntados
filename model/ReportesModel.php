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

        // If $result is true, the insert succeeded
        if ($result === true) {
            // get last inserted id safely
            $lastId = mysqli_insert_id($this->conexion);
            return $lastId ?: true; // return true even if insert_id = 0
        }

        // Log or debug to see what happened
        error_log("Error creating report: " . $this->conexion->error);
        return null;
    }

}
?>