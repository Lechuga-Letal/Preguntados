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
        $idUsuario = $this->usuarioModel->obtenerIdUsuarioPorNombre($usuario); 

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


    public function desafiar() {
        $idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario'];
        $jugadores = $this->usuarioModel->obtenerListadoDeJugadoresMenos($idUsuario);
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
        //Lo pruebo con este id hasta que haga la ruleta y lo mando por post
        $idCategoria = 1;
        $idTurno = $this->model->crearTurno($nombreUsuario, $idPartida, $idCategoria);
        $this->redirectModel->redirect("partida/mostrarPartida?idTurno=$idTurno");
        //todo: ver forma de cambiarle la URL
    }

    public function mostrarPartida(){
        $idTurno= $_GET["idTurno"];
        $_SESSION['turno']=$idTurno;

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
            $_SESSION['turno']=$turno;

            $lePego=$this->model->evaluarRespuestaDelTurno($opcionElegida,$turno);

            //verificar tiempo
            $fueraDelTiempo= $this->controlarTiempo();

            if(!$lePego || $fueraDelTiempo){
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
            $idTurno=$_SESSION['turno'] ?? null;
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

    public function inicioCronometro(){
        $_SESSION['cronometro'] = time(); // HORA DE INICIO tiempo 
    }

    public function duracionTiempoMaximoPorTurno(){
        $tiempoMaximoEnResponder = 5; // segundos
        return $tiempoMaximoEnResponder;
    }

    public function finCronometro(){
        $tiempoMaximo = $this->duracionTiempoMaximoPorTurno();
        $tiempoFin = $_SESSION['cronometro'] + $tiempoMaximo; //
        return $tiempoFin;
    }

    public function inicioCronometroAPI(){
        header('Content-Type: application/json');
        $this->inicioCronometro();
        $tiempoMaximoPorTurno = $this->duracionTiempoMaximoPorTurno();
        $tiempoInicio = $_SESSION['cronometro'];
        $tiempoFin = $this->finCronometro();

        $data = [
            'tiempoMaximoPorTurno' => $tiempoMaximoPorTurno,
            'tiempoInicio' => $tiempoInicio,
            'tiempoFin' => $tiempoFin //no se si es necesario aca, no lo usamos
        ];
            
        echo json_encode($data);
    }   
    
    public function controlarTiempo(){
        $terminarPartida = false;
        $turno=$_SESSION['turno'];
        $finCronometro = $this->finCronometro();
        $tiempoActual = time();
        if($finCronometro <= $tiempoActual){
            $terminarPartida = true;
            $this->redirectModel->redirect("partida/terminarPartida?idTurno=$turno");

        }
        echo $terminarPartida; //desde aca podemos terminarla
    }

    /*
    public function terminarPartidaPorTiempoMaximo(){
        $turno=$_SESSION['turno'];
        $this->redirectModel->redirect("partida/terminarPartida?idTurno=$turno");

    }*/
}
