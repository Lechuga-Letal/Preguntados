<?php

class PerfilController
{
    private $model;
    private $renderer;
    private $redirectModel;
    private $usuarioModel;

    public function __construct($model, $renderer, $redirectModel, $usuarioModel)
    {
        $this->model = $model;
        $this->renderer = $renderer;
        $this->redirectModel = $redirectModel;
        $this->usuarioModel = $usuarioModel;
    }

    public function base()
    {
        $this->getPerfil();
    }

    public function getPerfil()
    {
        $idPerfil = $_GET['id'] ?? $_SESSION["id"];
        $usuario= $this->usuarioModel->getUsuarioById($idPerfil);
        $usuarioSesion = $this->usuarioModel->getUsuarioById($_SESSION["id"]);
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $esEditor= $this->usuarioEsEditor($usuarioSesion);
        $idSesion = $_SESSION["id"];
        $data = [
            "usuario" => $usuario,
            "usuarioSesion" => $usuarioSesion,
            "id" => $idSesion,
            "idPerfil" => $idPerfil,
            "foto_perfil" => $foto,
            "esEditor" => $esEditor
        ];

        $this->renderer->render("perfil", $data);
    }

    private function usuarioEsEditor($usuarioSesion)
    {
        return isset($usuarioSesion["rol"])
            && strtolower($usuarioSesion["rol"]) === "editor";
    }

}