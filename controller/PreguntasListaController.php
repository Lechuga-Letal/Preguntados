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
        $this->obtenerOperacion();
    }

    public function obtenerOperacion()
    {
        $tipo = $_GET["tipo"] ?? 'activas';

        switch ($tipo) {
            case 'reportes':
                $preguntas = $this->preguntasModel->obtenerPreguntasReportadas();
                break;
            case 'sugeridas':
                $preguntas = $this->preguntasModel->obtenerPreguntasSugeridas();
                break;
            default:
                $preguntas = $this->preguntasModel->obtenerPreguntasConRespuestas();
                break;
        }

        $this->listarPreguntas($preguntas);
    }

    public function listarPreguntas($preguntas)
        {
        $agrupadas = [];
        $tmp = [];

            if(!empty($preguntas)) {
            foreach ($preguntas as $fila) {
                $id = $fila['pregunta_id'];

                if (!isset($tmp[$id])) {
                    $tmp[$id] = [
                        'pregunta_id' => $id,
                        'pregunta' => $fila['pregunta'],
                        'respuestas' => [],
                        'cantidad_reportes' => $fila['cantidad_reportes'] ?? 0,
                        'usuario_sugirio' => $fila['usuario_sugirio'] ?? null,
                        'tipo' => $_GET['tipo'] ?? 'activas' //Hay que volver a pasar el parametro, no se bien porque
                    ];
                }

                if (!empty($fila['respuesta'])) {
                    $tmp[$id]['respuestas'][] = [
                        'descripcion' => $fila['respuesta'],
                        'es_correcta' => $fila['es_correcta']
                    ];
                }
                
            }
        }
        $agrupadas = array_values($tmp);

        $this->renderer->render("preguntasLista", ['preguntas' => $agrupadas]);
    }
}
