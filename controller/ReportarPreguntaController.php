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

        if (isset($_GET['idPregunta']) && is_numeric($_GET['idPregunta'])) {

            $_SESSION['id_pregunta_reportar'] = (int) $_GET['idPregunta'];

            if (isset($_GET['success'])) $_SESSION['report_success'] = true;
            if (isset($_GET['error'])) $_SESSION['report_error'] = true;

            $this->redirectModel->redirect("reportarPregunta"); 
            return;
        }

        if (!isset($_SESSION['id_pregunta_reportar'])) {
            $this->renderer->render('error', ['mensaje' => 'ID de pregunta inválido.']);
            return;
        }

        $id_pregunta = $_SESSION['id_pregunta_reportar'];

        $pregunta = $this->preguntasModel->obtenerPreguntaPorId($id_pregunta);
        $respuestas = $this->respuestasModel->obtenerRespuestasPorPregunta($id_pregunta);

        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $idUsuario = $_SESSION['id'] ?? $_SESSION['usuario'];
        $usuario = $this->usuarioModel->getUsuarioById($idUsuario);
        $usuarioNombre = $usuario['usuario'];

        if (!$pregunta) {
            unset($_SESSION['id_pregunta_reportar']);
            $this->renderer->render('error', ['mensaje' => 'La pregunta no existe.']);
            return;
        }

        $data = [
            'pregunta' => $pregunta,
            'respuestas' => $respuestas,
            "usuario" => $usuarioNombre,
            "foto_perfil" => $foto
        ];

        if (!empty($_SESSION['report_success'])) {
            $data['mensaje'] = '✅ El reporte fue enviado correctamente.';
            unset($_SESSION['report_success']);
        }
        if (!empty($_SESSION['report_error'])) {
            $data['error'] = '❌ No se pudo enviar el reporte.';
            unset($_SESSION['report_error']);
        }

        $this->renderer->render('reportarPregunta', $data);
    }

    public function listarPreguntasPartida(){
        if (!isset($_SESSION['usuario'])) {
            $this->redirectModel->redirect('login/loginForm');
            return;
        }

        $idJugador=$this->usuarioModel->obtenerIdUsuarioPorNombre($_SESSION['usuario']);
        $preguntasDelJugador=$this->preguntasModel->obtenerPreguntasDeLaUltimaPartidaDelJugador($idJugador);

        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $idUsuario = $_SESSION['id'] ?? $_SESSION['usuario'];
        $usuario = $this->usuarioModel->getUsuarioById($idUsuario);
        $usuarioNombre = $usuario['usuario'];
        
        $data=[
            "preguntas"=>$preguntasDelJugador,
            "foto_perfil" => $foto,
            "usuario" => $usuarioNombre
        ];

        $this->renderer->render('reportarPreguntaLista', $data);
    }

    public function crearReporteDePregunta()
    {
        if (!isset($_SESSION['usuario'])) {
            $this->redirectModel->redirect('login/loginForm');
            return;
        }

        $usuarioData = $this->usuarioModel->getUsuarioByNombreUsuario($_SESSION['usuario']);
        $id_usuario = $usuarioData['id_usuario'] ?? $usuarioData['id'] ?? null;

        $id_pregunta = $_POST['id_pregunta'] ?? null;
        $motivo = trim($_POST['motivo'] ?? '');


        $id_reporte = $this->reportesModel->crearReporte($id_pregunta, $id_usuario, $motivo);
        $this->redirectModel->redirect("reportarPregunta/getPregunta");
    }
}