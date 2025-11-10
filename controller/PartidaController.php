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

    public function jugar() {
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
        $this->renderer->render("ruleta");
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

    //TODO anda siempre y cuando no haya una partida previa
    public function crearTurno() {
        var_dump($_SESSION);
        $usuarioNombre = $_SESSION['usuario'];
        $usuarioId = $this->usuarioModel->obtenerIdUsuarioPorNombre($usuarioNombre);
        $categoria = $_POST['categoria']?? $_SESSION['categoria_actual'] ?? null;
        if ($categoria) $_SESSION['categoria_actual'] = $categoria;


        $idPartida = $_SESSION['id'] ?? $this->model->crearPartida($usuarioId);
        $_SESSION['id'] = $idPartida;

        $mapaCategorias = [
            'Deportes' => 1,
            'Entretenimiento' => 2,
            'Informática' => 3,
            'Matemáticas' => 4,
            'Historia' => 5
        ];
        $idCategoria = $mapaCategorias[$categoria] ?? null;

        $idTurno = $this->model->crearTurno($usuarioId, $idPartida, $idCategoria);
        $_SESSION['turno'] = $idTurno;
        $pregunta = $_SESSION['pregunta_turno'] ?? null;
        $idPregunta = $pregunta['id_pregunta'];
        $descripcionPregunta = $pregunta['descripcion'];
        $respuestas = $this->model->obtenerRespuestasDelTurno($idTurno);
        $correctas = $this->model->mostrarCantidadCorrectasPorPartida($idTurno, $idPartida, $usuarioId);

        $model = [
            'id_turno' => $idTurno,
            'pregunta' => $descripcionPregunta,
            'idPregunta' => $idPregunta,
            'Respuestas' => $respuestas,
            'cantidadCorrectas' => $correctas,
            'nombreOponente' => 'Desconocido'
        ];

        $this->renderer->render("partida", $model);
    }

    public function mostrarPartida() {
        $idTurno = $_GET["idTurno"];
        $_SESSION['turno'] = $idTurno;

        $idPartida = $_SESSION['id'] ?? $this->model->obtenerIdPartidaPorTurno($idTurno);
        $usuarioNombre = $_SESSION['usuario'];
        $idUsuario = $this->usuarioModel->obtenerIdUsuarioPorNombre($usuarioNombre);
        $correctas = $this->model->mostrarCantidadCorrectasPorPartida($idTurno, $idPartida, $idUsuario);

        $model = [
            'id_turno' => $idTurno,
            'cantidadCorrectas' => $correctas,
            'pregunta' => $this->model->obtenerDescripcionDeLaPreguntaPorTurno($idTurno),
            'Respuestas' => $this->model->obtenerRespuestasDelTurno($idTurno),
            'nombreOponente' => $this->model->getNombreOponente($idTurno) ?? 'Desconocido'
        ];
        $this->renderer->render("partida", $model);
    }

    public function evaluarTurno() {
        $opcionElegida = $_GET['respuestaElegida'] ?? null;
        $turno = $_GET['turno'] ?? null;
        $idPregunta = $_GET['idPregunta'] ?? null;
        $_SESSION['turno'] = $turno;

        $lePego = $this->model->evaluarRespuesta($opcionElegida, $turno); // aca se rompio

        $fueraDelTiempo= $this->controlarTiempo();


        if (!$lePego) {
            $this->redirectModel->redirect("partida/terminarPartida?idTurno=$turno");
            return;
        }

        $_SESSION['preguntas_respondidas'] = ($_SESSION['preguntas_respondidas'] ?? 0) + 1;
        $nombreUsuario = $this->model->obtenerNombreUsuarioPorTurno($turno);
        $categoria = $_SESSION['categoria_actual'] ?? '';

        $mapaCategorias = [
            'Deportes' => 1,
            'Entretenimiento' => 2,
            'Informática' => 3,
            'Matemáticas' => 4,
            'Historia' => 5
        ];
        $idCategoria = $mapaCategorias[$categoria] ?? null;

        $idUsuario = $this->usuarioModel->obtenerIdUsuarioPorNombre($nombreUsuario);
        $this->model->acreditarAcierto($turno, $idPregunta);

        if ($_SESSION['preguntas_respondidas'] >= 5) {
            unset($_SESSION['preguntas_respondidas']);
            unset($_SESSION['categoria_actual']);
            $this->redirectModel->redirect("partida/iniciarPartida");
            return;
        }
//        $idTurno = $_GET["idTurno"];
        $idTurno = $_GET["turno"];
        $_SESSION['turno'] = $idTurno;

        $idPartida = $this->model->obtenerIdPartidaPorTurno($idTurno);
        $URL = "partida/crearTurno?nombreUsuario=$nombreUsuario&idPartida=$idPartida&categoria=$categoria";
        $this->redirectModel->redirect($URL);
    }

    public function terminarPartida(){
        $usuarioNombre = $_SESSION['usuario'];
        $usuarioId = $this->usuarioModel->obtenerIdUsuarioPorNombre($usuarioNombre);
        $idTurno=$_SESSION['turno'] ?? null;
        $idPartida=$this->model->obtenerIdPartidaPorTurno($idTurno);
        $cantidadCorrectas=$this->model->mostrarCantidadCorrectasPorPartida($idTurno, $idPartida, $usuarioId);

        $this->model->finalizarPartida($idPartida, $cantidadCorrectas);

        $this->borradoDeDatosPartidaEnSession();

        var_dump($_SESSION);
        //TODO: cambiarle el nombre al metodo
        $this->renderer->render("partidaFinalizada",
            ["puntaje"=>$cantidadCorrectas,
                'nombreOponente' =>$this->model->getNombreOponente($idTurno)?? 'Desconocido',
                "pregunta"=>$this->model->obtenerDescripcionDeLaPreguntaPorTurno($idTurno),
                "cantidadCorrectas" => $cantidadCorrectas,
                "respuestaCorrecta"=>$this->model->obtenerDescripcionDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno)]);
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
        echo $terminarPartida;    }

    public function mostrarTiempo(){
        header('Content-Type: application/json');
        $tiempoRestante = $this->obtenerTiempoTranscurrido();
        echo json_encode(['tiempoRestante' => $tiempoRestante]);
    }

    /*
    public function terminarPartidaPorTiempoMaximo(){
        $turno=$_SESSION['turno'];
        $this->redirectModel->redirect("partida/terminarPartida?idTurno=$turno");

    }*/

    public function mensajeDeRevisionDeErrores(){
        var_dump("llegue");
        die();
    }

    public function borradoDeDatosPartidaEnSession(){
        $valoresPartidaBorradosSession = ["id","cronometro", "pregunta_turno", "turno", "categoria_actual", "preguntas_respondidas"];
        foreach ($valoresPartidaBorradosSession as $clave) {
            if (isset($_SESSION[$clave])) {
                unset($_SESSION[$clave]);
            }
        }
    }
}
