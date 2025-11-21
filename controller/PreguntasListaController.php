<?php

class PreguntasListaController
{
    private $model;
    private $renderer;
    private $redirectModel; 
    private $preguntasModel; 
    private $categoriasModel; 
    public function __construct($model, $renderer, $redirectModel, $preguntasModel, $categoriasModel)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
        $this->redirectModel = $redirectModel;
        $this->preguntasModel = $preguntasModel;    
        $this->categoriasModel = $categoriasModel;
    }

    public function base()
    {
        $this->obtenerOperacion();
    }

    public function obtenerOperacion()
    {
        $tipo = $_GET["tipo"] ?? 'activas';
        $categoria = $_GET["categoria"] ?? null;

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
        $this->listarPreguntas($preguntas, $categoria);
    }

    public function listarPreguntas($preguntas, $categoria)
    {

        if ($categoria) {
            $preguntas = array_filter($preguntas, function($p) use ($categoria) {
                return isset($p['categoria']) && $p['categoria'] === $categoria;
            });
        }

        $agrupadas = [];
        $tmp = [];

        if(!empty($preguntas)) {
            foreach ($preguntas as $fila) {
                $id = $fila['pregunta_id'];

                if (!isset($tmp[$id])) {
                    $tmp[$id] = [
                        'pregunta_id' => $id,
                        'pregunta' => $fila['pregunta'],
                        'categoria' => $fila['categoria'] ?? null,
                        'respuestas' => [],
                        'cantidad_reportes' => $fila['cantidad_reportes'] ?? 0,
                        'usuario_sugirio' => $fila['usuario_sugirio'] ?? null
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

        $categorias = $this->categoriasModel->getAllCategorias();

        foreach ($categorias as &$cat) {
            $cat['seleccionada'] = ($cat['nombre'] === $categoria);
        }

        $this->renderer->render("preguntasLista", [
            'preguntas'       => $agrupadas,
            'categorias'      => $categorias
        ]);
    }

}
