<?php

class NuevaPreguntaController
{
    private $model;
    private $renderer;
    private $redirectModel; 
    private $preguntasModel;
    private $respuestasModel;
    private $usuarioModel;

    public function __construct($model, $renderer, $redirectModel, $preguntasModel, $respuestasModel,$usuarioModel)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
        $this->redirectModel = $redirectModel;
        $this->preguntasModel = $preguntasModel;
        $this->respuestasModel = $respuestasModel;
        $this->usuarioModel = $usuarioModel;
    }

    public function base()
    {
        $this->inicio();
    }

    public function inicio()
    {
        if (!isset($_SESSION['usuario'])) {
            $this->redirectModel->redirect('login/loginForm');
            return;
        }

        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $rol = $_SESSION['rol'] ?? 'Jugador';

        $data = [
        ];
        if($rol== 'Jugador') {
            $data = ['Editor' => false,
                "foto_perfil" => $foto];
        } else {
            $data = ['Editor' => true,
                "foto_perfil" => $foto];
        }

        $this->renderer->render("nuevaPregunta", $data);
    }

    public function guardarPregunta()
    {
        $descripcion = $_POST['descripcion'] ?? '';
        $id_categoria = $_POST['id_categoria'] ?? 0;
        $respuestas = $_POST['respuestas'] ?? [];
        $indiceCorrecta = $_POST['es_correcta'] ?? 0;
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';

        if (!$this->validarCampos($descripcion, $respuestas, $indiceCorrecta)) {
            $this->renderer->render('nuevaPregunta', ['error' => 'Todos los campos son obligatorios.']);
            return;
        }

        $id_pregunta = $this->preguntasModel->insertarPregunta($descripcion, $id_categoria);

        foreach ($respuestas as $i => $texto) {
            $esCorrecta = ($i == $indiceCorrecta) ? 1 : 0;
            $this->respuestasModel->insertarRespuesta($texto, $esCorrecta, $id_pregunta);
        } 

        $data = [
            'mensaje' => 'La pregunta fue agregada exitosamente',
            'Editor' => true,
            "foto_perfil" => $foto
        ];
        $this->renderer->render('nuevaPregunta', $data);
    }

    public function sugerirPregunta() 
    {
        $descripcion = $_POST['descripcion'] ?? '';
        $id_categoria = $_POST['id_categoria'] ?? 0;
        $respuestas = $_POST['respuestas'] ?? [];
        $indiceCorrecta = $_POST['es_correcta'] ?? 0;
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';

        if (!$this->validarCampos($descripcion, $respuestas, $indiceCorrecta)) {
            $this->renderer->render('nuevaPregunta', ['error' => 'Todos los campos son obligatorios.']);
            return;
        }

        $usuarioData = $this->usuarioModel->getUsuarioByNombreUsuario($_SESSION['usuario']);

        if (!$usuarioData || empty($usuarioData['id'])) {
            $this->renderer->render('nuevaPregunta', ['error' => 'Usuario no válido.']);
            return;
        }

        $id_usuario = $usuarioData['id'];
        
        $id_pregunta = $this->preguntasModel->insertarPreguntaSugerida($descripcion, $id_categoria, $id_usuario);

        foreach ($respuestas as $i => $texto) {
            $esCorrecta = ($i == $indiceCorrecta) ? 1 : 0;
            $this->respuestasModel->insertarRespuestaSugerida($texto, $esCorrecta, $id_pregunta);
        } 
        $data = [
            'mensaje' => 'La pregunta fue sugerida exitosamente',
            'Editor' => false,
            "foto_perfil" => $foto
        ];
        $this->renderer->render('nuevaPregunta', $data);
    }

    private function validarCampos($descripcion, $respuestas, $indiceCorrecta)
    {
        return !(empty($descripcion) || empty($respuestas) || $indiceCorrecta === null);
    }
}
