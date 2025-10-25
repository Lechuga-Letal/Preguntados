<?php

class inicioAdminController
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
        $this->inicioAdmin();
    }

    public function inicioAdmin()
    {
        if (!isset($_SESSION["usuario"])) {
            header("Location: /login/loginForm");
            exit;
        }

        if ($_SESSION["rol"] !== "Administrador") {
            header("Location: /inicio");
            exit;
        }

        $totalUsuarios   = $this->model->contarUsuarios();
        $usuariosDia     = $this->model->contarUsuarios('dia');
        $usuariosSemana  = $this->model->contarUsuarios('semana');
        $usuariosMes     = $this->model->contarUsuarios('mes');
        $usuariosAnio    = $this->model->contarUsuarios('anio');

        $usuarios = $this->model->obtenerUsuarios();

        $data = [
            "usuario" => $_SESSION["usuario"],
            "usuariosNuevos" => $totalUsuarios,
            "totalUsuarios" => $totalUsuarios,
            "usuariosDia" => $usuariosDia,
            "usuariosSemana" => $usuariosSemana,
            "usuariosMes" => $usuariosMes,
            "usuariosAnio" => $usuariosAnio,
            "usuarios" => $usuarios
        ];

        $this->renderer->render("inicioAdmin", $data);
    }

    public function cambiarRol()
    {
        if (!isset($_SESSION["usuario"])) {
            header("Location: /login/loginForm");
            exit;
        }

        if ($_SESSION["rol"] !== "Administrador") {
            header("Location: /inicio");
            exit;
        }

        if (isset($_POST["id_usuario"])) {
            $idUsuario = $_POST["id_usuario"];
            $this->model->cambiarRolUsuario($idUsuario);
        }

        header("Location: /inicioAdmin/inicioAdmin");
        exit;
    }
}

