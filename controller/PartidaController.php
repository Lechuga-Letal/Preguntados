<?php

class PartidaController{

    private $model;
    private $renderer;
    private $redirectModel;

    public function __construct($model, $renderer, $redirectModel){

        $this->model = $model;
        $this->renderer = $renderer;
        $this->redirectModel = $redirectModel;
    }

    public function base(){
        $this->jugar();
    }

    public function jugar()
    {
        if (!isset($_SESSION['usuario'])) {
            header("Location: /login/loginForm");
            exit();
        }

        $this->renderer->render("oponente");
    }

    public function iniciarPartida() {

        if (!isset($_SESSION['usuario'])) {
            header("Location: /login/loginForm");
            exit();
        }

    $idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario'];

     $idPartida = $this->model->crearPartida($idUsuario);

     $_SESSION['id'] = $idPartida;

    $this->renderer->render("partida");
}

//Según mi logica se terminan de completar los datos en la tabla de la bd cuando el usuario responde mal
// y de ahí se llamaria a este metodo para hacer el update. Pero como no estoy segura de como hacerlo
// por ahora solo finaliza cuando sale de la vista partida

    public function finalizarPartida() {
        $puntajeFinal = $_SESSION['puntaje'] ?? 0;
        $idPartida = $_SESSION['id'] ?? null;

        if ($idPartida) {
            $this->model->finalizarPartida($idPartida, $puntajeFinal);
        }

        unset($_SESSION['id']);
        unset($_SESSION['puntaje']);
    }

    public function desafiar() {
        $idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario'];
        $jugadores = $this->model->obtenerListadoDeJugadores($idUsuario);
        $this->renderer->render("desafiar", ["usuarios" => $jugadores]);
    }
}
