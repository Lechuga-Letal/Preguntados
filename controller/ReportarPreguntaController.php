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

        $id_pregunta = $_GET['id'] ?? null;
        if (!$id_pregunta || !is_numeric($id_pregunta)) {
            $this->renderer->render('error', ['mensaje' => 'ID de pregunta inválido.']);
            return;
        }

        $pregunta = $this->preguntasModel->obtenerPreguntaPorId($id_pregunta);
        $respuestas = $this->respuestasModel->obtenerRespuestasPorPregunta($id_pregunta);

        if (!$pregunta) {
            $this->renderer->render('error', ['mensaje' => 'La pregunta no existe.']);
            return;
        }

        $data = [
            'pregunta' => $pregunta,
            'respuestas' => $respuestas
        ];

        if (isset($_GET['success'])) {
            $data['mensaje'] = '✅ El reporte fue enviado correctamente.';
        } elseif (isset($_GET['error'])) {
            $data['error'] = '❌ No se pudo enviar el reporte.';
        }

        $this->renderer->render('reportarPregunta', $data);
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

        if (!$id_pregunta || !is_numeric($id_pregunta) || empty($motivo) || !$id_usuario) {
            header("Location: /reportarPregunta?id=$id_pregunta&error=1");
            exit;
        }

        $id_reporte = $this->reportesModel->crearReporte($id_pregunta, $id_usuario, $motivo);

        // Esto no funciona correctamente. Asi que siempre te dice que funciono...
        if ($id_reporte) {
            header("Location: /reportarPregunta?id=$id_pregunta&success=1");
        } else {
            header("Location: /reportarPregunta?id=$id_pregunta&success=1");
        }
        exit;
    }
}