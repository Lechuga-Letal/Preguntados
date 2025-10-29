<?php

class PartidaController{

    private $model;
    private $renderer;
    private $redirectModel;
    private $usuarioModel;
    public function __construct($model, $renderer, $redirectModel, $usuarioModel){

        $this->model = $model;
        $this->renderer = $renderer;
        $this->redirectModel = $redirectModel;
        $this->usuarioModel = $usuarioModel;
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

        $this->renderer->render("oponente"); //Capaz lo podemos llamar Modo (Para no confundir con oponentes usuarios)
    }

    public function misDesafios() {
        if (!isset($_SESSION['usuario'])) {
            header("Location: /login/loginForm");
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $idUsuario = $this->model->obtenerIdUsuarioPorNombre($usuario); 

        $dataDePartidasFinalizadas = $this->model->obtenerDataDePartidasPorEstado($idUsuario, "finalizada");
        $dataDePartidasEnEspera = $this->model->obtenerDataDePartidasPorEstado($idUsuario, "en curso");
        //Falta mover mayoria de codigo al modelo

        $data = [
            //De momento solo se utiliza la data del oponente, nada de la partida. 
            "usuario" => $usuario,
            "partidas_finalizadas" => $dataDePartidasFinalizadas, 
            "partidas_enCurso" => $dataDePartidasEnEspera
        ];
        $this->renderer->render("misDesafios", $data);
    }

//Según mi logica se terminan de completar los datos en la tabla de la bd cuando el usuario responde mal
// y de ahí se llamaria a este metodo para hacer el update. Pero como no estoy segura de como hacerlo
// por ahora solo finaliza cuando sale de la vista partida

    public function desafiar() {
        $idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario'];
        $jugadores = $this->model->obtenerListadoDeJugadoresMenos($idUsuario);
        $this->renderer->render("desafiar", ["usuarios" => $jugadores]);
    }

    public function iniciarPartida() 
    {
        if (!isset($_SESSION['usuario'])) {
            header("Location: /login/loginForm");
            exit();
        }

        $usuarioNomrbe = $_SESSION['usuario']['id'] ?? $_SESSION['usuario']; //no devuelve id...
        $idOponente = $_POST['id_oponente'] ?? 0; //Cero en caso de bot(? 

        $idPartida = $this->model->crearPartida($usuarioNomrbe, $idOponente);

        $_SESSION['id'] = $idPartida;

        $oponente = $this->usuarioModel->getUsuarioById($idOponente);

        $data = [
            'idOponente' => $idOponente, 
            'nombreOponente' => $oponente['usuario'] ?? 'Desconocido'        
        ];

        $this->renderer->render("partida", $data);
    }

    public function obtenerPregunta(){
        header('Content-Type: application/json');

        $pregunta = $this->model->obtenerPregunta();

        echo json_encode($pregunta);
    }

    public function finalizarPartida() {
        $puntajeFinal = $_SESSION['puntaje'] ?? 0;
        $idPartida = $_SESSION['id'] ?? null;

        if ($idPartida) {
            $this->model->finalizarPartida($idPartida, $puntajeFinal);
        }

        unset($_SESSION['id']);
        unset($_SESSION['puntaje']);
    }
}
