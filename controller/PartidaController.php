<?php
class PartidaController{

    private $model;
    private $renderer;
    private $redirectModel;
    private $usuarioModel;
    private $preguntasModel;
    private $obtenerIdUsuarioPorNombre;
    private $categoriasModel; 

    public function __construct($model, $renderer, $redirectModel, $usuarioModel, $preguntasModel, $categoriasModel){

        $this->model = $model;
        $this->renderer = $renderer;
        $this->redirectModel = $redirectModel;
        $this->usuarioModel = $usuarioModel;
        $this->preguntasModel = $preguntasModel;
        $this->categoriasModel = $categoriasModel;
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

        if($_SESSION["id"]){
            $puntaje=$this->model->obtenerTotalAciertosPorPartida($_SESSION["id"]);
            $this->model->finalizarPartida($_SESSION["id"],$puntaje);
            $this->borradoDeDatosPartidaEnSession();
        }

        $categorias = $this->categoriasModel->getCategoriasActivas();

        $data =  [
            "categorias" => $categorias
        ];

        $this->renderer->render("ruleta", $data);
    }


    public function misDesafios() {
        if (!isset($_SESSION['usuario'])) {
            header("Location: /login/loginForm");
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $idUsuario = $this->usuarioModel->obtenerIdUsuarioPorNombre($usuario);
        $dataDePartidasFinalizadas = $this->model->obtenerPartidasFinalizadasPorId($idUsuario);
//        $dataDePartidasFinalizadas = $this->model->obtenerDataDePartidasPorEstado($idUsuario, "finalizada");
        $dataDePartidasEnEspera = $this->model->obtenerDataDePartidasPorEstado($idUsuario, "en curso");
        //Falta mover mayoria de codigo al modelo

//        var_dump($dataDePartidasFinalizadas);
        $data = [
            //De momento solo se utiliza la data del oponente, nada de la partida. 
            "usuario" => $usuario,
            "partidas_finalizadas" => $dataDePartidasFinalizadas,
//            "partidas_enCurso" => $dataDePartidasEnEspera
        ];

        $this->renderer->render("misDesafios", $data);
    }


    public function desafiar() {
        $idUsuario = $_SESSION['usuario']['id'] ?? $_SESSION['usuario'];
        $jugadores = $this->usuarioModel->obtenerListadoDeJugadoresMenos($idUsuario);
        $this->renderer->render("desafiar", ["usuarios" => $jugadores]);
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
        $this->inicioCronometroAPI();// inicia el cronometro al cargar la partida asi es solouna vez(?)
        $this->renderer->render("partida", $model);
    }

    public function crearTurno() {
        //si hay un turno pendiente o si el tiempo del turno no termino
        $usuarioId = $this->usuarioModel->obtenerIdUsuarioPorNombre($_SESSION["usuario"]);
        if (isset($_SESSION["pregunta_turno"])) {
            $tiempoRestante=15-(time()-$_SESSION["cronometro"]);

            $model = [
                'id_turno' => $_SESSION["turno"],
                'pregunta' => $_SESSION["pregunta_turno"]["descripcion"],
                'idPregunta' => $_SESSION["pregunta_turno"]["id_pregunta"],
                'Respuestas' => $this->model->obtenerRespuestasDelTurno($_SESSION["turno"]),
                'cantidadCorrectas'=> $this->model->mostrarCantidadCorrectasPorPartida($_SESSION["turno"], $_SESSION['id'], $usuarioId),
                'nombreOponente' => 'Desconocido',
                'tiempo' =>$tiempoRestante
            ];

        }else{
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

        }
        $this->renderer->render("partida", $model);
    }

    public function evaluarTurno() {
        $opcionElegida = $_GET['respuestaElegida'] ?? null;
        $turno = $_GET['turno'] ?? null;
        $idPregunta = $_GET['idPregunta'] ?? null;
        $_SESSION['turno'] = $turno;

        $nombreUsuario = $this->model->obtenerNombreUsuarioPorTurno($turno);
        $idUsuario = $this->usuarioModel->obtenerIdUsuarioPorNombre($nombreUsuario);

        $lePego = $this->model->evaluarRespuesta($opcionElegida, $turno);
        $this->preguntasModel->actualizarCantidades($idPregunta, $lePego);
        $this->preguntasModel->actualizarNivel($idPregunta);

        $this->model->actualizarNivelJugador($idUsuario,$turno);
        $fueraDelTiempo= $this->controlarTiempo();

        if($fueraDelTiempo || isset($_GET["tiempo"])){
            $this->borradoDeDatosPregunta();
//            $this->model->acreditarFueraPasadoDeTiempo($turno, $idPregunta);
            $this->redirectModel->redirect("partida/terminarPartida?idTurno=$turno");
            return;
        }

        if (!$lePego) {
            $this->model->acreditarIntentoFallido($turno, $idPregunta);
            $this->model->actualizarNivelJugador($idUsuario,$turno);
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
        $this->model->actualizarNivelJugador($idUsuario,$turno);

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
        $this->borradoDeDatosPregunta();
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
        $tiempoMaximoEnResponder = 15; // segundos
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
          /*  Estos son los datos que muestra, setea el inicio del cronometro, duracion y fin
        */

        echo json_encode($data);
    }

    public function controlarTiempo(){
//        $terminarPartida = false;
//        $turno=$_SESSION['turno'];
//        $finCronometro = $this->finCronometro();
//        $tiempoActual = time();
//        $this->mensajeDeRevisionDeErrores();
//        if($finCronometro <= $tiempoActual){
//            $terminarPartida = true;
//            $this->terminarPartida();
//
//        }
//        echo $terminarPartida;
        $terminarPartida = false;
        $finCronometro = $this->finCronometro();
        $tiempoActual = time();
        if($finCronometro <= $tiempoActual){
            $terminarPartida = true;
        }
        return $terminarPartida;
    }

    public function mostrarTiempo(){
        header('Content-Type: application/json');
        $tiempoRestante = $this->model->obtenerTiempoTranscurrido();
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
    public function borradoDeDatosPregunta(){
//        $valoresPartidaBorradosSession = ["cronometro", "pregunta_turno", "turno", "categoria_actual", "preguntas_respondidas"];
        $valoresPartidaBorradosSession = ["pregunta_turno"];
        foreach ($valoresPartidaBorradosSession as $clave) {
            if (isset($_SESSION[$clave])) {
                unset($_SESSION[$clave]);
            }
        }
    }
}
