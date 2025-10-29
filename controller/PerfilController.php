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
        $this->renderer->render("perfil");
    }




}