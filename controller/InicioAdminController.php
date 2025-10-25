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
        $this->inicio();
    }

    public function inicio()
    {
        $data = [
            "usuario" => $_SESSION["usuario"],
            "cantidadJugadores" => $this->model->contarJugadores(),
            "partidasJugadas" => $this->model->contarPartidas(),
            "preguntasTotales" => $this->model->contarPreguntas(),
            "preguntasCreadas" => $this->model->contarPreguntasCreadas(),
            "usuariosNuevos" => $this->model->contarUsuariosNuevos(),
            "usuarios" => $this->model->obtenerEstadisticasUsuarios()
        ];

        $this->renderer->render("inicioAdmin", $data);
    }


}