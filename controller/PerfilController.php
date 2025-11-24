<?php

class PerfilController
{
    private $model;
    private $renderer;
    private $redirectModel;
    private $usuarioModel;
    private $categoriasModel; 
    public function __construct($model, $renderer, $redirectModel, $usuarioModel, $categoriasModel)
    {
        $this->model = $model;
        $this->renderer = $renderer;
        $this->redirectModel = $redirectModel;
        $this->usuarioModel = $usuarioModel;
        $this->categoriasModel = $categoriasModel; 
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

        $categoriasYNivelDeUsuario = $this->categoriasModel->getAllCategorias(); 

        foreach($categoriasYNivelDeUsuario as &$cat) {
            $cat['nivel'] = $this->usuarioModel->getNivelUsuarioPorCategoria($idPerfil, $cat['id_categoria']);
        }

        $nivelGeneral = $this->usuarioModel->getNivelUsuarioGeneral($idPerfil); 

        $data = [
            "usuario" => $usuario,
            "usuarioSesion" => $usuarioSesion,
            "id" => $idSesion,
            "idPerfil" => $idPerfil,
            "foto_perfil" => $foto,
            "esEditor" => $esEditor,

            "nivelPorCategoria" => $categoriasYNivelDeUsuario,
            "nivelGeneral" => $nivelGeneral
        ];

        $this->renderer->render("perfil", $data);
    }

    private function usuarioEsEditor($usuarioSesion)
    {
        return isset($usuarioSesion["rol"])
            && strtolower($usuarioSesion["rol"]) === "editor";
    }

}