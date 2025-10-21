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

        /*
        if (empty($usuario) || empty($password)) {
            $this->renderer->render('login', ['error' => 'Debes completar todos los campos']);
            return;
        } */

        $resultado = $this->model->getUserWith($usuario, $password);

        if (!empty($resultado) && count($resultado) > 0) {
            $_SESSION['usuario'] = $usuario;
            $this->redirectModel->redirect('inicio/');
        } else {
            $this->renderer->render('login', ['error' => 'Usuario o contraseña incorrectos']);
        }
    }
}

