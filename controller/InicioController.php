<?php

class InicioController
{
    private $model;
    private $renderer;
    private $redirectModel; 

    public function __construct($model, $renderer, $redirectModel)
    {
        $this->model = $model;     
        $this->renderer = $renderer; 
        $this->redirectModel = $redirectModel;
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

    public function logout()
    {
        $this->model->logout();
        $this->redirectModel->redirect("login/loginForm"); 
    }

    public function ingresoPorMail(){
        //TODO darle comportamiento al metodo como deberia
        $usuario = "admin";
        $this->renderer->render("inicio",
                                ["usuario" => $usuario]);
    }
}
