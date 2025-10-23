<?php

class PreguntasListaController
{
    private $model;
    private $renderer;
    private $redirectModel; 
    private $preguntasModel; 
    public function __construct($model, $renderer, $redirectModel, $preguntasModel)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
        $this->redirectModel = $redirectModel;
        $this->preguntasModel = $preguntasModel;    
    }

    public function base()
    {
        $this->listarPreguntas();
    }

    public function listarPreguntas()
        {
        $preguntas = $this->preguntasModel->obtenerPreguntasConRespuestas();

        $agrupadas = [];
        $tmp = [];

        foreach ($preguntas as $fila) {
            $id = $fila['pregunta_id'];

            if (!isset($tmp[$id])) {
                $tmp[$id] = [
                    'pregunta_id' => $id,
                    'pregunta' => $fila['pregunta'],
                    'respuestas' => []
                ];
            }

            if (!empty($fila['respuesta'])) {
                $tmp[$id]['respuestas'][] = [
                    'descripcion' => $fila['respuesta'],
                    'es_correcta' => $fila['es_correcta']
                ];
            }
        }

        // Convert associative array to sequential array
        $agrupadas = array_values($tmp);

        $this->renderer->render("preguntasLista", ['preguntas' => $agrupadas]);

        }

}
