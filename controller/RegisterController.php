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

        $nombre_completo = $_POST['nombre_completo'] ?? '';
        $anio_nacimiento = $_POST['anio_nacimiento'] ?? '';
        $sexo= $_POST['sexo'] ?? '';
        $pais= $_POST['pais'] ?? '';
        $ciudad= $_POST['ciudad'] ?? '';
        $usuario= $_POST['usuario'] ?? '';
        $mail= $_POST['mail'] ?? '';
        $pass1= $_POST['password1'] ?? '';
        $pass2= $_POST['password2'] ?? '';
        $latitud = $_POST['latitud'] ?? '';
        $longitud = $_POST['longitud'] ?? '';

        $foto_perfil = null;
        if (!empty($_FILES['foto_perfil']['name'])) {
            $imagen = "imagenes/";
            $foto_perfil = $imagen . basename($_FILES['foto_perfil']['name']);
            move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $foto_perfil);
        }

        if (empty($nombre_completo) || empty($anio_nacimiento) || empty($sexo) || empty($pais) || empty($ciudad) ||
            empty($usuario) || empty($mail) || empty($pass1) || empty($pass2) || empty($latitud) || empty($longitud) ) {
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

        $this->model->createUser($nombre_completo, $anio_nacimiento, $sexo, $pais, $ciudad, $usuario, $mail, $pass1, $foto_perfil);

        $this->redirectToLogin();
    }

    public function redirectToLogin()
    {
        header("Location: /Preguntados/login/loginForm");
        exit;
    }

}

