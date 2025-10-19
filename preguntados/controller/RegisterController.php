<?php

class RegisterController
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
        $this->register();
    }

    public function registerForm()
    {
        $this->renderer->render("register");
    }

 public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->registerForm();
            return;
        }

        $usuario   = $_POST['usuario'] ?? '';
        $mail      = $_POST['mail'] ?? '';
        $pass1     = $_POST['password1'] ?? '';
        $pass2     = $_POST['password2'] ?? '';

        if (empty($usuario) || empty($mail) || empty($pass1) || empty($pass2)) {
            $this->renderer->render('register', ['error' => 'Todos los campos son obligatorios']);
            return;
        }

        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->renderer->render('register', ['error' => 'Email no válido']);
            return;
        }

        if ($pass1 !== $pass2) {
            $this->renderer->render('register', ['error' => 'Las contraseñas no coinciden']);
            return;
        }

        if ($this->model->userExists($usuario, $mail)) {
            $this->renderer->render('register', ['error' => 'Usuario o email ya registrado']);
            return;
        }

        $this->model->createUser($usuario, $mail, $pass1);

        $this->redirectToLogin();
    }

    public function redirectToLogin()
    {
        header("Location: /");
        exit;
    }

}

