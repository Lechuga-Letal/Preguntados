<?php

class NuevaPreguntaController
{
    private $model;
    private $renderer;
    private $redirectModel; 
    private $preguntasModel;
    private $respuestasModel;

    public function __construct($model, $renderer, $redirectModel, $preguntasModel, $respuestasModel)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
        $this->redirectModel = $redirectModel;
        $this->preguntasModel = $preguntasModel;
        $this->respuestasModel = $respuestasModel;
    }

    public function base()
    {
        $this->inicio();
    }

    public function inicio()
    {
        $this->renderer->render("nuevaPregunta");
    }

    public function guardarPregunta()
    {
        $descripcion = $_POST['descripcion'] ?? '';
        $id_categoria = $_POST['id_categoria'] ?? 0;
        $respuestas = $_POST['respuestas'] ?? [];
        $indiceCorrecta = $_POST['es_correcta'] ?? 0;

        if (empty($descripcion) || empty($respuestas) || $indiceCorrecta === null) {
            $this->renderer->render('nuevaPregunta', ['error' => 'Todos los campos son obligatorios.']);
            return;
        }

        $id_pregunta = $this->preguntasModel->insertarPregunta($descripcion, $id_categoria);

        foreach ($respuestas as $i => $texto) {
            $esCorrecta = ($i == $indiceCorrecta) ? 1 : 0;
            $this->respuestasModel->insertarRespuesta($texto, $esCorrecta, $id_pregunta);
        } $this->renderer->render('nuevaPregunta', ['mensaje' => 'La pregunta fue agregada exitosamente']);
    }
}
