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

    public function obtenerUsuarios(){
        $query = "SELECT id, usuario, mail, nombre_completo, anio_nacimiento, sexo, pais, rol FROM usuarios";
        $usuarios = $this->conexion->query($query);

        foreach ($usuarios as &$usuario) {
            $usuario['esJugador'] = strtolower($usuario['rol']) === 'jugador';
        }

        return $usuarios;
    }

    public function cambiarRolUsuario($idUsuario){
        $query = "UPDATE usuarios SET rol = 'Editor' WHERE id = $idUsuario";
        return $this->conexion->query($query);
    }

    public function contarUsuariosPorSexo() {
        $query = "SELECT sexo, COUNT(*) as total FROM usuarios GROUP BY sexo";
        $result = $this->conexion->query($query);

        $data = [
            "masculino" => 0,
            "femenino" => 0,
            "Prefiero no cargarlo" => 0
        ];

        foreach ($result as $row) {
            $sexo = strtolower(trim($row['sexo']));
            if ($sexo === 'masculino') {
                $data['masculino'] = $row['total'];
            } elseif ($sexo === 'femenino') {
                $data['femenino'] = $row['total'];
            } else {
                $data['Prefiero no cargarlo'] += $row['total'];
            }
        }

        return $data;
    }


    public function contarUsuariosPorGrupoDeEdad() {
        $query = "
        SELECT 
            CASE
                WHEN TIMESTAMPDIFF(YEAR, CONCAT(anio_nacimiento,'-01-01'), CURDATE()) BETWEEN 0 AND 12 THEN 'Niños'
                WHEN TIMESTAMPDIFF(YEAR, CONCAT(anio_nacimiento,'-01-01'), CURDATE()) BETWEEN 13 AND 17 THEN 'Adolescentes'
                WHEN TIMESTAMPDIFF(YEAR, CONCAT(anio_nacimiento,'-01-01'), CURDATE()) BETWEEN 18 AND 25 THEN 'Jóvenes'
                WHEN TIMESTAMPDIFF(YEAR, CONCAT(anio_nacimiento,'-01-01'), CURDATE()) BETWEEN 26 AND 45 THEN 'Adultos'
                WHEN TIMESTAMPDIFF(YEAR, CONCAT(anio_nacimiento,'-01-01'), CURDATE()) BETWEEN 46 AND 60 THEN 'Adultos Mayores'
                ELSE 'Jubilados'
            END AS grupo_edad,
            COUNT(*) AS total FROM usuarios GROUP BY grupo_edad ORDER BY grupo_edad; ";

        $result = $this->conexion->query($query);
        return $result;
    }

    public function contarUsuariosPorPais() {
        $query = " SELECT pais, COUNT(*) AS total FROM usuarios GROUP BY pais ORDER BY pais";
        return $this->conexion->query($query);
    }

    public function contarUsuariosPorRol() {
        $query = " SELECT rol, COUNT(*) AS total FROM usuarios GROUP BY rol";
        return $this->conexion->query($query);
    }

    public function preguntasTotales() {
        $query = "SELECT COUNT(*) AS total FROM pregunta";
        $result = $this->conexion->query($query);

        if (!empty($result) && isset($result[0]['total'])) {
            return (int) $result[0]['total'];
        }
        return 0;
    }

    public function partidasFinalizadas(){
        $query = "SELECT COUNT(*) AS total FROM partidas WHERE estado='finalizada'";
        $result= $this->conexion->query($query);
        if (!empty($result) && isset($result[0]['total'])) {
            return (int) $result[0]['total'];
        }
        return 0;
    }

    public function preguntasReportadas(){
        $query = "SELECT COUNT(*) AS total FROM reporte";
        $result= $this->conexion->query($query);
        if (!empty($result) && isset($result[0]['total'])) {
            return (int) $result[0]['total'];
        }
        return 0;
    }

    public function getGraficoBase64($nombreGrafico)
    {
        $ruta = __DIR__ . "/../public/graficos/$nombreGrafico.png";
        if (!file_exists($ruta)) return '';
        $tipo = pathinfo($ruta, PATHINFO_EXTENSION);
        $data = file_get_contents($ruta);
        return 'data:image/' . $tipo . ';base64,' . base64_encode($data);
    }

    public function getTodosGraficos()
    {
        $graficos = [
            'usuarios_por_sexo' => 'Usuarios por sexo',
            'usuarios_por_edad' => 'Usuarios por grupo de edad',
            'usuarios_por_pais' => 'Usuarios por país',
            'usuarios_por_rol' => 'Usuarios por rol',
            'usuarios_por_periodo' => 'Usuarios por periodo'
        ];

        $resultado = [];
        foreach ($graficos as $archivo => $titulo) {
            $resultado[] = [
                'titulo' => $titulo,
                'base64' => $this->getGraficoBase64($archivo)
            ];
        }

        return $resultado;
    }

    public function getCategoriasYPorcentaje() 
    {
        $query = "SELECT nombre, AVG(nivel) as promedio_nivel FROM categoria
            LEFT OUTER JOIN niveljugadorporcategoria 
            ON categoria.id_categoria = niveljugadorporcategoria.id_categoria
            GROUP BY categoria.id_categoria, nombre";
        $result = $this->conexion->query($query);
        return $result; 
    }

//    public function obtenerDatosParaPdf($css, $usuarios, $graficos){
//        $html = ' <html>
//    <head>
//        <style>' . $css . '</style>
//    </head>
//    <body>';
//
//        $html .= '<h1>Panel de Administración</h1>';
//        $html .= '<h2>Gestión de Usuarios</h2>';
//        $html .= '<table><thead><tr>
//                        <th>Id</th><th>Usuario</th><th>Mail</th><th>Nombre completo</th>
//                        <th>Año nacimiento</th><th>Sexo</th><th>Pais</th>
//                        <th>% Correctas</th><th>Rol</th>
//                    </tr> </thead> <tbody>';
//        foreach ($usuarios as $u) {
//            $html .= '<tr>
//                    <td>'.$u['id'].'</td>
//                    <td>'.$u['usuario'].'</td>
//                    <td>'.$u['mail'].'</td>
//                    <td>'.$u['nombre_completo'].'</td>
//                    <td>'.$u['anio_nacimiento'].'</td>
//                    <td>'.$u['sexo'].'</td>
//                    <td>'.$u['pais'].'</td>
//                    <td>'.$u['porcentaje_correctas'].'</td>
//                    <td>'.$u['rol'].'</td>
//                  </tr>';
//        }
//        $html .= '</tbody></table>';
//
//        $grafPeriodo = $graficos[4];
//        $html .= '<h3>'.$grafPeriodo['titulo'].'</h3>';
//        $html .= '<img src="'.$grafPeriodo['base64'].'">';
//        $html .= '<div class="page-break"></div>';
//
//        $graficosRestantes = array_slice($graficos, 0, 4);
//        for ($i = 0; $i < count($graficosRestantes); $i += 2) {
//            $html .= '<div>';
//            $html .= '<h3>'.$graficosRestantes[$i]['titulo'].'</h3>';
//            $html .= '<img src="'.$graficosRestantes[$i]['base64'].'">';
//            if (isset($graficosRestantes[$i+1])) {
//                $html .= '<h3>'.$graficosRestantes[$i+1]['titulo'].'</h3>';
//                $html .= '<img src="'.$graficosRestantes[$i+1]['base64'].'">';
//            }
//            $html .= '</div>';
//            if ($i + 2 < count($graficosRestantes)) {
//                $html .= '<div class="page-break"></div>';
//            }
//        }
//
//        $html .= '</body></html>';
//        return $html;
//    }
}


