<?php

class InicioEditorController
{
    private $model;
    private $renderer;

    public function __construct($model, $renderer)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
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
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $data =[
            "foto_perfil"=> $foto
        ];
        if ($_SESSION["rol"] === "Editor") {
            $this->renderer->render("inicioEditor", $data);
            exit;
        } else {
            header("Location: /login/loginForm");
        }


    }

}
