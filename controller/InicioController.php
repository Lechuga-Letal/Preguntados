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
        if (!isset($_SESSION['usuario'])) {
            $this->redirectModel->redirect('login/loginForm');
            return;
        }

        $usuario = $_SESSION['usuario'];
        $rol = $_SESSION['rol'] ?? 'Jugador';

        if ($rol === 'Administrador') {
            $this->renderer->render("InicioAdmin", ["usuario" => $usuario, "rol" => $rol]);
        } else {
            $this->renderer->render("inicio", ["usuario" => $usuario, "rol" => $rol]);
        }
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
