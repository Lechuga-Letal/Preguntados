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
    { //Cuenta esto como logica de negocio?? 
        $today = date('Y-m-d');
        $minDate = date('Y-m-d', strtotime('-99 years'));

        $this->renderer->render('register', [
            'minDate' => $minDate,
            'maxDate' => $today
        ]);
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

            $required = [$nombre_completo, $anio_nacimiento, $pais, $usuario, $mail, $pass1, $pass2];
            
            foreach ($required as $field) {
                if (empty($field)) {
                    $this->renderer->render('register', ['error' => 'Todos los campos son obligatorios']);
                    return;
                }
            }

            $sexo = $_POST['sexo'] ?? '';
            if (empty($sexo)) {
                $sexo = "Prefiero no cargarlo";
            }

            if (empty($ciudad) && (empty($latitud) || empty($longitud))) { //El usuario usa el mapa o una ciudad
                $this->renderer->render('register', ['error' => 'Debes completar ciudad o latitud/longitud']);
                return;
            }

            $ciudad_db = !empty($ciudad) ? $ciudad : "Lat: $latitud, Lon: $longitud";

            if(!$this->model->userExists($usuario, $mail)) {    
                $this->model->createUser($nombre_completo, $anio_nacimiento, $sexo, $pais, $ciudad_db, $usuario, $mail, $pass1, $foto_perfil);

                $this->redirectToLogin();
            } else {
                $this->renderer->render('register', ['error' => 'El usuario o email ya está registrado']);
            }
        }

    public function redirectToLogin()
    {
        header("Location: /Preguntados/login/loginForm");
        exit;
    }

}

