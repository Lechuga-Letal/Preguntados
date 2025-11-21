<?php
require_once __DIR__ . "/../jpgraph/src/jpgraph.php";
require_once __DIR__ . "/../jpgraph/src/jpgraph_bar.php";
require_once __DIR__ . "/../dompdf/autoload.inc.php";
use Dompdf\Dompdf;
use Dompdf\Options;

class inicioAdminController{
    private $model;
    private $renderer;
    private $categoriaModel;

    public function __construct($model, $renderer, $categoriaModel){
        $this->model = $model;
        $this->renderer = $renderer;
        $this->categoriaModel = $categoriaModel; 
    }

    public function base(){
        $this->inicioAdmin();
    }



    public function inicioAdmin(){
        if (!isset($_SESSION["usuario"])) {
            header("Location: /login/loginForm");
            exit;
        }

        if ($_SESSION["rol"] !== "Administrador") {
            header("Location: /inicio");
            exit;
        }

        $this->cargarData(null); 
    }

    public function cargarData($mensajeCat) {
        $totalUsuarios= $this->model->contarUsuarios();
        $partidasJugadas= $this->model->partidasFinalizadas();
        $preguntasTotales= $this->model->preguntasTotales();
        $preguntasReportadas= $this->model->preguntasReportadas();

        $usuariosDia= $this->model->contarUsuarios('dia');
        $usuariosSemana= $this->model->contarUsuarios('semana');
        $usuariosMes= $this->model->contarUsuarios('mes');
        $usuariosAnio= $this->model->contarUsuarios('anio');
        $usuarios= $this->model->obtenerUsuarios();

        $sexoData= $this->model->contarUsuariosPorSexo();
        $edadData= $this->model->contarUsuariosPorGrupoDeEdad();
        $paisData= $this->model->contarUsuariosPorPais();
        $rolData= $this->model->contarUsuariosPorRol();

        $baseDir= __DIR__ . "/../public/graficos";

        $rutaUsuariosPeriodo = $baseDir . "/usuarios_por_periodo.png";
        $rutaSexo= $baseDir . "/usuarios_por_sexo.png";
        $rutaEdad= $baseDir . "/usuarios_por_edad.png";
        $rutaPais= $baseDir . "/usuarios_por_pais.png";
        $rutaRol= $baseDir . "/usuarios_por_rol.png";
        $labelsPeriodo= ["Último día", "Última semana", "Último mes", "Último año"];
        $valoresPeriodo= [$usuariosDia, $usuariosSemana, $usuariosMes, $usuariosAnio];

        $this->generarGraficoBarras(
            $labelsPeriodo,
            $valoresPeriodo,
            "Usuarios por periodo",
            $rutaUsuariosPeriodo
        );

        $this->generarGraficoBarras(
            array_keys($sexoData),
            array_values($sexoData),
            "Usuarios por sexo",
            $rutaSexo
        );

        $edadLabels= array_column($edadData, "grupo_edad");
        $edadValores= array_column($edadData, "total");

        $this->generarGraficoBarras(
            $edadLabels,
            $edadValores,
            "Usuarios por edad",
            $rutaEdad
        );

        $paisLabels= array_column($paisData, "pais");
        $paisValores= array_column($paisData, "total");

        $this->generarGraficoBarras(
            $paisLabels,
            $paisValores,
            "Usuarios por país",
            $rutaPais
        );

        $rolLabels= array_column($rolData, "rol");
        $rolValores = array_column($rolData, "total");

        $this->generarGraficoBarras(
            $rolLabels,
            $rolValores,
            "Usuarios por rol",
            $rutaRol
        );

        $categoriasAct = $this->categoriaModel->getCategoriasActivasData();
        $categoriasInact = $this->categoriaModel->getCategoriasInactivasData(); 

        $MIN_PREGUNTAS = $this->categoriaModel->getMinPreguntas();

        foreach ($categoriasInact as &$cat) {
            $cat['faltantes'] = max(0, $MIN_PREGUNTAS - $cat['cantidad_preguntas']);
        }

        $foto = $_SESSION['foto_perfil'] ?? 'public/imagenes/usuarioImagenDefault.png';

        $data = [
            "usuario" => $_SESSION["usuario"],

            "usuariosNuevos"      => $totalUsuarios,
            "partidasJugadas"     => $partidasJugadas,
            "preguntasTotales"    => $preguntasTotales,
            "preguntasReportadas" => $preguntasReportadas,
            "usuarios" => $usuarios,
            "categoriasAct" => $categoriasAct,
            "categoriasInact" => $categoriasInact,

            "foto_perfil" => $foto,
            "grafUsuariosSexo" => "/public/graficos/usuarios_por_sexo.png",
            "grafUsuariosEdad" => "/public/graficos/usuarios_por_edad.png",
            "grafUsuariosPais" => "/public/graficos/usuarios_por_pais.png",
            "grafUsuariosRol"  => "/public/graficos/usuarios_por_rol.png",
            "grafUsuariosPeriodo" => "/public/graficos/usuarios_por_periodo.png",

            "mensajeCat" => $mensajeCat
        ];

        $this->renderer->render("inicioAdmin", $data);
    }

    public function cambiarRol(){
        if (isset($_POST["id_usuario"])) {
            $this->model->cambiarRolUsuario($_POST["id_usuario"]);
        }
        header("Location: /inicioAdmin/inicioAdmin");
    }

    private function generarGraficoBarras($labels, $valores, $titulo, $archivo){
        if (file_exists($archivo)) {
            unlink($archivo);
        }

        $graph = new Graph(600, 350);
        $graph->SetScale('textlin');
        $graph->title->Set($titulo);
        $graph->xaxis->SetTickLabels($labels);

        $bar = new BarPlot($valores);
        $bar->SetColor("black");
        $bar->SetFillColor("#6495ED");
        $graph->Add($bar);

        $graph->Stroke($archivo);
    }

    public function crearCategoria()
    {
        $nombreCategoria = $_POST['categoriaNombre'] ?? null; 
        $fotoCategoria = null; 
        if(!empty($_FILES['categoriaImagen']['name'])) {
            $imagen = "public/imagenes/";
            $fotoCategoria = $imagen . basename($_FILES['categoriaImagen']['name']);
            move_uploaded_file($_FILES['categoriaImagen']['tmp_name'], $fotoCategoria); 
        }
        $sePudo = $this->categoriaModel->crearNuevaCategoria($nombreCategoria, $fotoCategoria);
        if(!$sePudo) { //De momento sin mensajes especificos segun error
            $this->cargarData("No se puso agregar la categoria.");
        } else {
            $this->cargarData("Se agrego la categoria exitosamente.");
        }
    }

    public function generarPDF(){
        $usuarios = $this->model->obtenerUsuarios();
        $graficos = $this->model->getTodosGraficos();

        $cssFile = __DIR__ . '/../public/css/pdfStyle.css';
        $css = file_exists($cssFile) ? file_get_contents($cssFile) : '';

//        $html = $this->model->obtenerDatosParaPdf($css, $usuarios, $graficos);
        $html = ' <html>
    <head>
        <style>' . $css . '</style>
    </head>
    <body>';

        $html .= '<h1>Panel de Administración</h1>';
        $html .= '<h2>Gestión de Usuarios</h2>';
        $html .= '<table><thead><tr>
                        <th>Id</th><th>Usuario</th><th>Mail</th><th>Nombre completo</th>
                        <th>Año nacimiento</th><th>Sexo</th><th>Pais</th>
                        <th>% Correctas</th><th>Rol</th>
                    </tr> </thead> <tbody>';
        foreach ($usuarios as $u) {
            $html .= '<tr>
                    <td>'.$u['id'].'</td>
                    <td>'.$u['usuario'].'</td>
                    <td>'.$u['mail'].'</td>
                    <td>'.$u['nombre_completo'].'</td>
                    <td>'.$u['anio_nacimiento'].'</td>
                    <td>'.$u['sexo'].'</td>
                    <td>'.$u['pais'].'</td>
                    <td>'.$u['porcentaje_correctas'].'</td>
                    <td>'.$u['rol'].'</td>
                  </tr>';
        }
        $html .= '</tbody></table>';

        $grafPeriodo = $graficos[4];
        $html .= '<h3>'.$grafPeriodo['titulo'].'</h3>';
        $html .= '<img src="'.$grafPeriodo['base64'].'">';
        $html .= '<div class="page-break"></div>';

        $graficosRestantes = array_slice($graficos, 0, 4);
        for ($i = 0; $i < count($graficosRestantes); $i += 2) {
            $html .= '<div>';
            $html .= '<h3>'.$graficosRestantes[$i]['titulo'].'</h3>';
            $html .= '<img src="'.$graficosRestantes[$i]['base64'].'">';
            if (isset($graficosRestantes[$i+1])) {
                $html .= '<h3>'.$graficosRestantes[$i+1]['titulo'].'</h3>';
                $html .= '<img src="'.$graficosRestantes[$i+1]['base64'].'">';
            }
            $html .= '</div>';
            if ($i + 2 < count($graficosRestantes)) {
                $html .= '<div class="page-break"></div>';
            }
        }

        $html .= '</body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("panel_administracion.pdf", ["Attachment" => 0]);
    }
}
?>