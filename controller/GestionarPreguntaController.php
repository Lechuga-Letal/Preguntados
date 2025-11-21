<?php

class GestionarPreguntaController
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
        $id = $_GET['id'] ?? null;
        $tipo = $_GET['tipo'] ?? 'activa';
        $action = $_GET['action'] ?? null;

        if ($id <= 0) {
            $this->redirectModel->redirect("editor/");
            return;
        }

        if ($action) {
            $this->procesarAccion($id, $action);
        }

        $this->cargarPregunta($id, $tipo);
    }

    private function procesarAccion($id, $action)
    {
        switch ($action) {
            case 'aceptar_sugerencia':
                $this->preguntasModel->darDeAltaASugerencia($id);
                $this->redirectModel->redirect('preguntasLista?tipo=activas');
                break;
            case 'rechazar_sugerencia':
                $this->preguntasModel->eliminarSugerenciaCompleta($id);
                $this->redirectModel->redirect('preguntasLista?tipo=sugeridas');
                break;
            case 'rechazar_reportes':
                $this->reportesModel->rechazarReportes($id);
                $this->redirectModel->redirect("gestionarPregunta?id=$id");
                break;
            case 'eliminar': 
                $id_categoria = $this->preguntasModel->getCategoriaDe($id);
                $this->preguntasModel->eliminarPregunta($id); 
                $this->categoriasModel->actualizarCategoria($id_categoria); 
                $this->redirectModel->redirect('preguntasLista?tipo=activas');
                break;
            case 'editar':
                $this->redirectModel->redirect("editarPregunta?id=$id");
                break;
        }
    }

    private function cargarPregunta($id, $tipo)
    {
        //Falta escribir el caso de pasar un id por url y que esa pregunta ya no esta en la bd
        if ($tipo === 'sugeridas') {
            $pregunta = $this->preguntasModel->obtenerSugerenciaPorId($id);
            $respuestas = $this->respuestasModel->obtenerRespuestasSugeridas($id);
            $reportes = [];
        } else {
            $pregunta = $this->preguntasModel->obtenerPreguntaPorId($id);
            $respuestas = $this->respuestasModel->obtenerRespuestasPorPregunta($id);
            $reportes = $this->reportesModel->obtenerReportesPorPregunta($id);
        }

        $data = [ //Ttodo lo necesario para que mustache presente la pregunta 
            'pregunta_id' => $id,
            'pregunta' => $pregunta,
            'respuestas' => $respuestas,
            'tipo' => ['is_sugerida' => $tipo === 'sugeridas'],
            'reports' => $reportes,
            'actions' => [
                'editar' => "/gestionarPregunta?id=$id&action=editar",
                'eliminar' => "/gestionarPregunta?id=$id&action=eliminar",
                'aceptar_sugerencia' => "/gestionarPregunta?id=$id&action=aceptar_sugerencia",
                'rechazar_sugerencia' => "/gestionarPregunta?id=$id&action=rechazar_sugerencia",
                'rechazar_reportes' => "/gestionarPregunta?id=$id&action=rechazar_reportes",
                'volver' => $_SERVER['HTTP_REFERER'] ?? "/preguntasLista" 
            ]
        ];
        $this->renderer->render("gestionarPregunta", $data);
    }
}