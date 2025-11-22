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

    public function crearReporteUsuario($idUsuarioQueReporta, $idUsuarioReportado, $motivo)
    {
        $query = "
        INSERT INTO reporteUsuario (id_usuario, id_usuarioReportado, descripcion)
        VALUES ('$idUsuarioQueReporta', '$idUsuarioReportado', '$motivo')
    ";

        $this->banearUsuario($idUsuarioReportado);
        $this->conexion->query($query);
    }


    public function banearUsuario($idUsuario)
    {
        $idUsuario = intval($idUsuario);

        $query = "
        UPDATE usuarios
        SET baneado = 1
        WHERE id = $idUsuario
    ";

        return $this->conexion->query($query);
    }

    public function banearDefinitivamente($id)
    {
        $id = intval($id);
        $query = "UPDATE usuarios SET baneado_definitivo = 1 WHERE id = $id";
        return $this->conexion->query($query);
    }

    public function desbanearUsuario($idUsuario)
    {
        $idUsuario = intval($idUsuario);

        $query = "
        UPDATE usuarios
        SET baneado = 0 
        WHERE id = $idUsuario
    ";

        return $this->conexion->query($query);
    }

    public function obtenerUsuariosBaneados() {
        $query = "
        SELECT
            u.id,
            u.usuario,
            u.nombre_completo,
            u.mail,
            u.sexo,
            u.foto_perfil,
            u.anio_nacimiento,
            u.rol,
            r.descripcion AS motivo,
            COUNT(r.id_reporte) AS cantidad_reportes
        FROM usuarios u
        JOIN reporteUsuario r ON r.id_usuarioReportado = u.id
        WHERE u.baneado = 1 AND u.baneado_definitivo=0
        GROUP BY u.id
        ORDER BY cantidad_reportes DESC
    ";
        return $this->conexion->query($query);
    }

}
