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

    public function cargarData($mensajeCat)
    {
        $metricas = $this->obtenerMetricas();
        $this->generarGraficos($metricas);

        $categorias = $this->obtenerCategoriasData();
        $data = $this->armarDataVista($metricas, $categorias, $mensajeCat);
        $this->renderer->render("inicioAdmin", $data);
    }

    private function obtenerMetricas()
    {
        return [
            "usuariosTotales"   => $this->model->contarUsuarios(),
            "partidasJugadas"   => $this->model->partidasFinalizadas(),
            "preguntasTotales"  => $this->model->preguntasTotales(),
            "preguntasReportadas" => $this->model->preguntasReportadas(),

            "usuariosPeriodo" => [
                "dia"    => $this->model->contarUsuarios('dia'),
                "semana" => $this->model->contarUsuarios('semana'),
                "mes"    => $this->model->contarUsuarios('mes'),
                "anio"   => $this->model->contarUsuarios('anio')
            ],

            "usuarios" => $this->model->obtenerUsuarios(),

            "sexo" => $this->model->contarUsuariosPorSexo(),
            "edad" => $this->model->contarUsuariosPorGrupoDeEdad(),
            "pais" => $this->model->contarUsuariosPorPais(),
            "rol"  => $this->model->contarUsuariosPorRol()
        ];
    }

    private function generarGraficos($m)
    {
        $baseDir = __DIR__ . "/../public/graficos";

        $this->generarGraficoBarras(
            ["Último día","Última semana","Último mes","Último año"],
            array_values($m["usuariosPeriodo"]),
            "Usuarios por periodo",
            "$baseDir/usuarios_por_periodo.png"
        );

        $this->generarGraficoBarras(
            array_keys($m["sexo"]),
            array_values($m["sexo"]),
            "Usuarios por sexo",
            "$baseDir/usuarios_por_sexo.png"
        );

        $this->generarGraficoBarras(
            array_column($m["edad"], "grupo_edad"),
            array_column($m["edad"], "total"),
            "Usuarios por edad",
            "$baseDir/usuarios_por_edad.png"
        );

        $this->generarGraficoBarras(
            array_column($m["pais"], "pais"),
            array_column($m["pais"], "total"),
            "Usuarios por país",
            "$baseDir/usuarios_por_pais.png"
        );

        $this->generarGraficoBarras(
            array_column($m["rol"], "rol"),
            array_column($m["rol"], "total"),
            "Usuarios por rol",
            "$baseDir/usuarios_por_rol.png"
        );
    }

    private function obtenerCategoriasData()
    {
        $act = $this->categoriaModel->getCategoriasActivasData();
        $inact = $this->categoriaModel->getCategoriasInactivasData();
        $min = $this->categoriaModel->getMinPreguntas();

        foreach ($inact as &$c) {
            $c["faltantes"] = max(0, $min - $c["cantidad_preguntas"]);
        }

        return [
            "activas" => $act,
            "inactivas" => $inact,
            "min" => $min
        ];
    }
    private function armarDataVista($metricas, $categorias, $mensajeCat)
    {
        $foto = $_SESSION['foto_perfil'] ?? 'public/imagenes/usuarioImagenDefault.png';

        return [
            "usuario" => $_SESSION["usuario"],

            "usuariosNuevos"      => $metricas["usuariosTotales"],
            "partidasJugadas"     => $metricas["partidasJugadas"],
            "preguntasTotales"    => $metricas["preguntasTotales"],
            "preguntasReportadas" => $metricas["preguntasReportadas"],
            "usuarios"            => $metricas["usuarios"],

            "categoriasAct"       => $categorias["activas"],
            "categoriasInact"     => $categorias["inactivas"],

            "foto_perfil" => $foto,

            "grafUsuariosSexo"    => "/public/graficos/usuarios_por_sexo.png",
            "grafUsuariosEdad"    => "/public/graficos/usuarios_por_edad.png",
            "grafUsuariosPais"    => "/public/graficos/usuarios_por_pais.png",
            "grafUsuariosRol"     => "/public/graficos/usuarios_por_rol.png",
            "grafUsuariosPeriodo" => "/public/graficos/usuarios_por_periodo.png",

            "mensajeCat" => $mensajeCat
        ];
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
        if(!$sePudo) {
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

        $html = $this->model->obtenerDatosParaPdf($css, $usuarios, $graficos);
        $options = new Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("panel_administracion.pdf", ["Attachment" => 0]);
    }

}
