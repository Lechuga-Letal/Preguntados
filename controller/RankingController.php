<?php

class RankingController
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
        $this->mejorJugador();
    }

    public function ranking()
    {
        $usuarios = $this->usuarioModel->getAllUsuarios();

        $data = [
            "usuarios" => $usuarios,
        ];
        $this->renderer->render("ranking", $data);
    }

    public function mejorJugador()
    {
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $usuarios = $this->usuarioModel->obtenerListaMejoresJugadores();
        $data = [
            "usuarios" => $usuarios,
            "foto_sesion" => $foto
        ];
        $this->renderer->render("ranking", $data);

    }

    public function obtenerListaMejoresJugadoresPorRango()
    {     
        $limite = $_GET['limite'] ?? null;
        $rango = $_GET['rango'] ?? null;
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';

        $usuarios = $this->usuarioModel->obtenerListaMejoresJugadoresPorRango($rango, $limite);
        $data = [
            "usuarios" => $usuarios,
            "foto_sesion" => $foto,
            "foto_perfil" => $foto,
            "rango" => $rango,
            "filtro"=> $limite
        ];
        $this->renderer->render("ranking", $data);

    }
    
    public function obtenerListaMejoresJugadoresPorCategoria()
    {     
        $limite = $_GET['limite'] ?? null;
        $categoria = $_GET['categoria'] ?? null;
        $foto = $_SESSION['foto_perfil'] ?? '/public/imagenes/usuarioImagenDefault.png';
        $usuarios = $this->usuarioModel->obtenerListaMejoresJugadoresPorCategoria($categoria, $limite);

        $data = [
            "usuarios" => $usuarios,
            "categoria" => $categoria,
            "foto_perfil" => $foto,
            "filtro"=> $limite,
            "foto_sesion" => $foto
        ];
        $this->renderer->render("ranking", $data);

    }

}

