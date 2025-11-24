<?php

use Dompdf\Renderer;

class EditarPreguntaController
{
    private $model;
    private $renderer;
    private $redirectModel; 
    private $preguntasModel;
    private $respuestasModel; 
    private $reportesModel; 
    private $categoriasModel;
    public function __construct($model, $renderer, $redirectModel, $preguntasModel, $respuestasModel, $reportesModel, $categoriasModel)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
        $this->redirectModel = $redirectModel;
        $this->preguntasModel = $preguntasModel;
        $this->respuestasModel = $respuestasModel;
        $this->reportesModel = $reportesModel;
        $this->categoriasModel = $categoriasModel; 
    }

    public function base()
    {
        $this->cargarVista();
    }

    public function cargarVista()
    {
        if (!isset($_SESSION["usuario"])) {
            header("Location: /login/loginForm");
            exit;
        }

        if ($_SESSION["rol"] != "Editor") {
            header("Location: /login/loginForm");
            exit;
        }

        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $id_pregunta = $_SESSION['busqueda'];

        $data = $this->cargarData($id_pregunta, $foto);

        $this->renderer->render("editarPregunta", $data);
    }

    public function guardarEdicion() 
    {
        $id_pregunta   = $_POST['id_pregunta'] ?? null;
        $descripcion   = $_POST['descripcion'] ?? null;
        $id_categoria  = $_POST['id_categoria'] ?? null;
        $respuestas    = $_POST['respuestas'] ?? null; 
        $id_correcta   = $_POST['es_correcta'] ?? null; 

        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';

        if (!$id_pregunta || !$descripcion || !$id_categoria || !$respuestas || !$id_correcta) {
            $data = ["error" => "Todos los campos son obligatorios."];
            return $this->renderer->render("editarPregunta", $data);
        }

        $this->preguntasModel->actualizarPregunta($id_pregunta, $descripcion, $id_categoria);

        $respuestasDB = $this->respuestasModel->obtenerRespuestasPorPregunta($id_pregunta);

        foreach ($respuestasDB as $i => $resp) {

            $id_respuesta = $resp['id_respuesta'];
            $nuevaDescripcion = $respuestas[$i];

            $correcta = ($id_respuesta == $id_correcta) ? 1 : 0;

            $this->respuestasModel->actualizarRespuesta(
                $id_respuesta,
                $nuevaDescripcion,
                $correcta
            );
        }
        $data = $this->cargarData($id_pregunta, $foto, "La pregunta se actualizó correctamente.");
        return $this->renderer->render("editarPregunta", $data);
    }

    private function cargarData($id_pregunta, $foto, $mensaje = null, $error = null)
    {
        $pregunta = $this->preguntasModel->obtenerPreguntaPorId($id_pregunta);
        $categoriaPregunta = $this->preguntasModel->getCategoriaDe($id_pregunta);

        $categorias = $this->categoriasModel->getAllCategorias();
        foreach ($categorias as &$categoria) {
            $categoria['selected'] = ($categoria['id_categoria'] == $categoriaPregunta->id_categoria);
        }

        $respuestas = $this->respuestasModel->obtenerRespuestasPorPregunta($id_pregunta);
        foreach ($respuestas as $i => &$resp) {
            $resp['numero'] = $i + 1;
            $resp['es_correcta'] = ($resp['correcta'] == 1);
        }

        return [
            "foto_perfil" => $foto,
            "pregunta" => $pregunta,
            "categorias" => $categorias,
            "respuestas" => $respuestas,
            "mensaje" => $mensaje,
            "error" => $error
        ];
    }
}