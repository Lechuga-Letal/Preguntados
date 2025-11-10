<?php

class LoginController
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
        $this->login();
    }

    public function loginForm()
    {
        $this->renderer->render("login");
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->loginForm();
            return;
        }

        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';


        if (empty($usuario) || empty($password)) {
            $this->renderer->render('login', ['error' => 'Debes completar todos los campos']);
            return;
        }

        $resultado = $this->model->getUserWith($usuario, $password);

        if (!empty($resultado)) {
            $_SESSION['usuario'] = $resultado['usuario'];                     
            $_SESSION['rol'] = $resultado['rol'];
            $_SESSION['sexo'] = $resultado['sexo'];               
            $_SESSION['foto_perfil'] = $resultado['foto_perfil']; 
            $this->vistaSegunRol();
        } else {
            $this->renderer->render('login', ['error' => 'Usuario o contraseña incorrectos']);
        }

    }

    private function vistaSegunRol()
    {
        $rol = $_SESSION['rol'] ?? 'Jugador';

        if ($rol === 'Administrador') {
            $this->redirectModel->redirect('InicioAdmin/inicio');
        } else {
            $this->redirectModel->redirect('inicio/');
        }
    }
}

