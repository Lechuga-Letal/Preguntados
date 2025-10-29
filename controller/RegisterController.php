<?php

class RegisterController
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
            $coordenadas= $_POST['coordenadas'] ?? ''; 
            
            $foto_perfil = null;
            if (!empty($_FILES['foto_perfil']['name'])) {
                $imagen = "public/imagenes/";
                $foto_perfil = $imagen . basename($_FILES['foto_perfil']['name']);
                move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $foto_perfil);
            }

            $required = [$nombre_completo, $anio_nacimiento, $ciudad, $pais, $usuario, $mail, $pass1, $pass2];
            
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

            if(!$this->model->userExists($usuario, $mail)) {
                $this->model->createUser($nombre_completo, $anio_nacimiento, $sexo, $pais, $ciudad, $coordenadas, $usuario, $mail, $pass1, $foto_perfil);

                $this->redirectModel->redirect("login/loginForm");
            } else {
                $this->renderer->render('register', ['error' => 'El usuario o email ya está registrado']);
            }
        }
}

