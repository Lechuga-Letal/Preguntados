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

        $URL="partida/crearTurno?nombreUsuario=$usuarioNomrbe&idPartida=$idPartida";
        $this->redirectModel->redirect($URL);
    }

    public function crearTurno(){
        $nombreUsuario= $_GET["nombreUsuario"];
        $idPartida=$_GET["idPartida"];

        $idTurno = $this->model->crearTurno($nombreUsuario,$idPartida);
        $this->redirectModel->redirect("partida/mostrarPartida?idTurno=$idTurno");
        //todo: ver forma de cambiarle la URL
    }

    public function mostrarPartida(){
        $idTurno= $_GET["idTurno"];

        $model = [
            'id_turno' => $idTurno,
            'cantidadCorrectas'=>$this->model->mostrarCantidadCorrectas($idTurno) ?? 0,
            'nombreOponente' =>$this->model->getNombreOponente($idTurno)?? 'Desconocido',
            'pregunta'=> $this->model->obtenerDescripcionDeLaPreguntaPorTurno($idTurno)
            ,'Respuestas'=> $this->model->obtenerRespuestasDelTurno($idTurno)
        ];

        $this->renderer->render("partida", $model);
    }

    public function evaluarTurno(){
        //TODO: Validaciones de Tiempo, si fue respondida antes, si la partida se termino, etc se hacen aca
            $opcionElegida=$_GET['respuestaElegida'];
            $turno=$_GET['turno'];

            $lePego=$this->model->evaluarRespuestaDelTurno($opcionElegida,$turno);

            if(!$lePego){
                //finalizar partida
                $this->redirectModel->redirect("partida/terminarPartida?idTurno=$turno");
                //TODO: cambiarle el nombre al metodo
            }else{
                $nombreUsuario=$this->model->obtenerNombreUsuarioPorTurno($turno);
                $idPartida=$this->model->obtenerIdPartidaPorTurno($turno);

                $this->model->acreditarAcierto($turno);

                $URL="partida/crearTurno?nombreUsuario=$nombreUsuario&idPartida=$idPartida";
                $this->redirectModel->redirect($URL);
            }
    }

      public function terminarPartida(){
            $idTurno=$_GET['idTurno'];
            $cantidadCorrectas=$this->model->mostrarCantidadCorrectas($idTurno);
            $idPartida=$this->model->obtenerIdPartidaPorTurno($idTurno);

          $this->model->finalizarPartida($idPartida, $cantidadCorrectas);

        //TODO: cambiarle el nombre al metodo
            $this->renderer->render("partidaFinalizada",
                                    ["puntaje"=>$cantidadCorrectas,
                                        'nombreOponente' =>$this->model->getNombreOponente($idTurno)?? 'Desconocido',
                                        "pregunta"=>$this->model->obtenerDescripcionDeLaPreguntaPorTurno($idTurno),
                                        "respuestaCorrecta"=>$this->model->obtenerDescripcionDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno)]);
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
