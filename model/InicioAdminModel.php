<?php
class InicioAdminModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function contarUsuarios($periodo = null) {
        $query = "SELECT COUNT(*) as total FROM usuarios";

        if ($periodo) {
            $intervalos = [
                'dia' => '1 DAY',
                'semana' => '1 WEEK',
                'mes' => '1 MONTH',
                'anio' => '1 YEAR'
            ];

            if (isset($intervalos[$periodo])) {
                $query .= " WHERE creacion >= DATE_SUB(NOW(), INTERVAL {$intervalos[$periodo]})";
            }
        }

        $result = $this->conexion->query($query);

        if (!$result || !isset($result[0]['total'])) {
            return 0;
        }

        return $result[0]['total'];
    }

    public function obtenerUsuarios()
    {
        $query = "SELECT id, usuario, mail, nombre_completo, anio_nacimiento, sexo, pais, rol FROM usuarios";
        $usuarios = $this->conexion->query($query);

        if (!$usuarios) {
            return [];
        }

        foreach ($usuarios as &$usuario) {
            $usuario['esJugador'] = strtolower($usuario['rol']) === 'jugador';
        }

        return $usuarios;
    }

    public function cambiarRolUsuario($idUsuario)
    {
        $query = "UPDATE usuarios SET rol = 'Editor' WHERE id = $idUsuario";
        return $this->conexion->query($query);
    }
}


