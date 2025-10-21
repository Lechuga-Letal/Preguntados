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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->model->getUserWith($_POST["usuario"], $_POST["password"]);

            if (sizeof($resultado) > 0) {
                $_SESSION["usuario"] = $_POST["usuario"];
                $this->redirectModel->redirect("inicio/");
            } else {
                $this->renderer->render("login", ["error" => "Usuario o clave incorrecta"]);
            }
        } else {
            $this->loginForm();
        }
    }
}

