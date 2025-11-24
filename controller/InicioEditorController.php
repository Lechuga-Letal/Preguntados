<?php

class InicioEditorController
{
    private $model;
    private $renderer;
    private $usuarioModel;
    public function __construct($model, $renderer, $usuarioModel)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
        $this->usuarioModel = $usuarioModel;
    }

    public function base()
    {
        $this->inicioEditor();
    }

    public function inicioEditor()
    {
        if (!isset($_SESSION["usuario"])) {
            header("Location: /login/loginForm");
            exit;
        }

        if ($_SESSION["rol"] === "Editor") {

            $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
            $idUsuario = $_SESSION['id'] ?? $_SESSION['usuario'];
            $usuario = $this->usuarioModel->getUsuarioById($idUsuario);
            $usuarioNombre = $usuario['usuario'];
            $data =[
                "foto_perfil"=> $foto,
                "usuario" => $usuarioNombre
            ];

            $this->renderer->render("inicioEditor", $data);
            exit;
        } else {
            header("Location: /login/loginForm");
        }
    }
}
