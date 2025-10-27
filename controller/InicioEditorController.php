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

        if ($_SESSION["rol"] === "Editor") {
            $this->renderer->render("inicioEditor");
            exit;
        } else {
            header("Location: /login/loginForm");
        }


    }

}
