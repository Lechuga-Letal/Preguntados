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

        $usuario = $_SESSION['usuario'] ?? '';
        $rol = $_SESSION['rol'] ?? 'Jugador';
        $sexo = $_SESSION['sexo'] ?? '';
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';

        if ($rol === 'Administrador') {
            $this->renderer->render("InicioAdmin", ["usuario" => $usuario, "rol" => $rol]);
        }
        //TODO: corregir esto que rompe la vista adminInicio

        $isEditor = ($rol === "Editor");

        $data = [
            "usuario" => $usuario,
            "sexo" => $sexo,
            "foto_perfil" => $foto,
            "isEditor" => $isEditor,
            "esMasculino" => ($sexo === "Masculino"),
            "esFemenino" => ($sexo === "Femenino"),
            "esNoBinario" => ($sexo != "Masculino" && $sexo != "Femenino")
        ];
        $this->renderer->render("inicio", $data);
    }

    public function logout()
    {
        $this->model->logout();
        $this->redirectModel->redirect("login/loginForm");
    }

    public function ingresoPorMail(){
        $usuario = "admin";
        $this->renderer->render("inicio",
                                ["usuario" => $usuario]);
    }
}
