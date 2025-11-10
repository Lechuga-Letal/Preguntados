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

    public function crearTurno() {
        $usuarioNombre = $_SESSION['usuario'];
        $usuarioId = $this->usuarioModel->obtenerIdUsuarioPorNombre($usuarioNombre);

        $categoria = $_GET['categoria'] ?? $_SESSION['categoria_actual'] ?? null;
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

        $this->inicioCronometro();
        $this->renderer->render("partida", $model);
    }

    public function evaluarTurno() {
        $opcionElegida = $_GET['respuestaElegida'] ?? null;
        $turno = $_GET['turno'] ?? null;
        $idPregunta = $_GET['idPregunta'] ?? null;
        $_SESSION['turno'] = $turno;
        $lePego = $this->model->evaluarRespuesta($opcionElegida, $turno);

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
        $idTurno = $_GET["idTurno"];
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

        //TODO: cambiarle el nombre al metodo
        $this->renderer->render("partidaFinalizada",
            ["puntaje"=>$cantidadCorrectas,
                'nombreOponente' =>$this->model->getNombreOponente($idTurno)?? 'Desconocido',
                "pregunta"=>$this->model->obtenerDescripcionDeLaPreguntaPorTurno($idTurno),
                "cantidadCorrectas" => $cantidadCorrectas,
                "respuestaCorrecta"=>$this->model->obtenerDescripcionDeLaRepuestaCorrectaDeLaPreguntaPorTurno($idTurno)]);
    }

    public function inicioCronometro(){
        $_SESSION['cronometro'] = time()+3;
        $cronometro = $_SESSION['cronometro'];
        return $cronometro;
    }

    public function obtenerTiempoTranscurrido(){
        if (isset($_SESSION['cronometro'])) {
            $tiempoInicio = $_SESSION['cronometro'];
            $tiempoActual = time();
            $tiempoTranscurrido = $tiempoInicio-$tiempoActual;
            if($tiempoTranscurrido <= 0){
                $this->terminarPartidaPorTiempoAgotado();
                $tiempoTranscurrido=0;
            }
            return $tiempoTranscurrido;
        }
        return null;
    }

    public function mostrarTiempo(){
        header('Content-Type: application/json');
        $tiempoRestante = $this->obtenerTiempoTranscurrido();
        echo json_encode(['tiempoRestante' => $tiempoRestante]);
    }

    public function terminarPartidaPorTiempoAgotado(){
        $idTurno=$_SESSION['turno'] ?? null;
        error_log("Terminando partida con idTurno: $idTurno");
        if ($idTurno) {
            $this->redirectModel->redirect("partida/terminarPartida?idTurno=$idTurno");
        }
    }

}
