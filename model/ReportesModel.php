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

        if ($result === true) {
            $lastId = mysqli_insert_id($this->conexion);
            return $lastId ?: true; 
        }

        error_log("Error creating report: " . $this->conexion->error);
        return null;
    }

    public function obtenerReportesPorPregunta($id_pregunta)
    {
        $id_pregunta = (int)$id_pregunta;

        $sql = "
            SELECT u.usuario, r.descripcion
            FROM reporte r
            JOIN usuarios u ON r.id_usuario = u.id
            WHERE r.id_pregunta = $id_pregunta
        ";

        $reportes = $this->conexion->query($sql);

        if (empty($reportes)) {
            return null;
        }

        return [
            'cantidad' => count($reportes),
            'reportes' => $reportes
        ];
    }

    public function rechazarReportes($id_pregunta) 
    {
        $id_pregunta = (int)$id_pregunta;
        if ($id_pregunta > 0) {
            $sql = "DELETE FROM reporte WHERE id_pregunta = $id_pregunta";
            $this->conexion->query($sql);
        }
    }

}
?>