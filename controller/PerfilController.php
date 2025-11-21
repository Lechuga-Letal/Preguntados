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
        $id = $_SESSION['id'];
        $usuario = $this->usuarioModel->getUsuarioById($_SESSION["id"]);
        $data = [
            "usuario" => $usuario,
            "id"=> $id];
        $this->renderer->render("perfil", $data);
    }
}