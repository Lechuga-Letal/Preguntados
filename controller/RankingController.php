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
            "usuarios" => $usuarios
        ];
        $this->renderer->render("ranking", $data);
    }

    //en base a los puntos obtenidos en las partidas general
    public function mejorJugador()
    {
        
        $usuarios = $this->usuarioModel->obtenerListaMejoresJugadores();
        $data = [
            "usuarios" => $usuarios
        ];
        $this->renderer->render("ranking", $data);

    }
}

