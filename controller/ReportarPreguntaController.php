<?php

class ReportarPreguntaController
{
    private $model;
    private $renderer;
    private $redirectModel;
    private $usuarioModel;
    private $preguntasModel;
    private $respuestasModel;
    private $reportesModel;
    public function __construct($model, $renderer, $redirectModel, $usuarioModel, $preguntasModel, $respuestasModel, $reportesModel)
    {
        $this->model = $model;
        $this->renderer = $renderer;
        $this->redirectModel = $redirectModel;
        $this->usuarioModel = $usuarioModel;
        $this->preguntasModel = $preguntasModel;
        $this->respuestasModel = $respuestasModel;
        $this->reportesModel = $reportesModel;
    }

    public function base()
    {
        $this->getPregunta();
    }

    public function getPregunta()
    {

        if (!isset($_SESSION['usuario'])) {
            $this->redirectModel->redirect('login/loginForm');
            return;
        }

        $id_pregunta = $_GET['id']; 
        $pregunta = $this->preguntasModel->obtenerPreguntaPorId($id_pregunta);
        $respuestas = $this->respuestasModel->obtenerRespuestasPorPregunta($id_pregunta);
        $data = [
            'pregunta' => $pregunta,
            'respuestas' => $respuestas
        ]; 
        $this->renderer->render("reportarPregunta", $data);
    }

    public function crearReporteDePregunta()
    {
        $nombre_usuairo = $_SESSION['usuario'];
        $id_usuario = $this->usuarioModel->getUsuarioByNombreUsuario($nombre_usuairo);
        
        $id_pregunta = $_POST['id_pregunta'] ?? null;
        $motivo = $_POST['motivo'] ?? '';
        $usuario = $_SESSION['usuario'] ?? null;

        if (!$id_pregunta || !$motivo || !$usuario) {
            $this->renderer->render('reportarPregunta', ['error' => 'Datos inválidos o sesión expirada.']);
            return;
        }
    
        $id_reporte = $this->reporteModel->crearReporte($id_pregunta, $id_usuario, $motivo);

        if ($id_reporte) {
            $data = ['mensaje' => 'El reporte fue enviado correctamente.'];
        } else {
            $data = ['error' => 'No se pudo enviar el reporte.'];
        }

        $this->renderer->render('reportarPregunta', $data);
    
    }

}