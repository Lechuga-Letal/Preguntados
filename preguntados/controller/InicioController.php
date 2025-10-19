<?php

class InicioController
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
        $this->inicio();
    }

    public function inicio()
    {
        $usuario = $_SESSION["usuario"] ?? "Invitado";
        $this->renderer->render("inicio", ["usuario" => $usuario]);
    }
}
