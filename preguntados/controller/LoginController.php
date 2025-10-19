<?php

class LoginController
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
        $this->login();
    }

    public function loginForm()
    {
        $this->renderer->render("login");
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->model->getUserWith($_POST["usuario"], $_POST["password"]);

            if (sizeof($resultado) > 0) {
                $_SESSION["usuario"] = $_POST["usuario"];
                echo"Deberia funcionar";
                $this->goToInicio();
            } else {
                echo"error";
                $this->renderer->render("login", ["error" => "Usuario o clave incorrecta"]);
            }
        } else {
            $this->loginForm();
        }
    }


    public function logout()
    {
        session_destroy();
        $this->goToIndex();
    }

    public function goToIndex()
    {
        header("Location: /");
        exit;
    }

    public function goToInicio()
    {
        header("Location: /?controller=inicio&method=base");
        exit;
    }
}

