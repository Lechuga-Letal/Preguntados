<?php

class PerfilController
{
    private $model;
    private $renderer;
    private $redirectModel;
    private $usuarioModel;

    public function __construct($model, $renderer, $redirectModel, $usuarioModel)
    {
        $this->model = $model;
        $this->renderer = $renderer;
        $this->redirectModel = $redirectModel;
        $this->usuarioModel = $usuarioModel;
    }

    public function base()
    {
        $this->getPerfil();
    }

    public function getPerfil()
    {
        $usuario = $this->usuarioModel->getUsuarioById($_GET["id"]);
        $data = [
            "usuario" => $usuario
        ];
        $this->renderer->render("perfil", $data);
    }
}