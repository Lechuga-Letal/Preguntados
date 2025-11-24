<?php

class ReportarUsuarioController {
    private $renderer;
    private $usuarioModel;
    private $reportesModel;

    private $redirectModel;

    public function __construct($renderer, $usuarioModel, $reportesModel, $redirectModel) {
        $this->renderer = $renderer;
        $this->usuarioModel = $usuarioModel;
        $this->reportesModel = $reportesModel;
        $this->redirectModel = $redirectModel;
    }
    public function base()
    {
        $this->formulario();
    }
    public function formulario() {
        $idReportado = $_GET["id"];
        $usuario = $this->usuarioModel->getUsuarioById($idReportado);

        foreach ($usuario as $key => $value) {
            if (is_null($value)) {
                $usuario[$key] = '';
            }
        }

        $data = $usuario;
        $this->renderer->render('reportarUsuario', $data);
    }

    public function enviar() {
        $idReportado = $_GET["id"];
        $usuario = $this->usuarioModel->getUsuarioById($idReportado);

        foreach ($usuario as $key => $value) {
            if (is_null($value)) {
                $usuario[$key] = '';
            }
        }

        $data = $usuario;
        $this->renderer->render('reportarMotivo', $data);
    }

    public function crearReporteUsuario()
    {
        if (!isset($_SESSION['id'])) {
            $this->redirectModel->redirect('login/loginForm');
            return;
        }

        $idUsuarioReportado = $_POST["id"] ?? null;
        $motivo = trim($_POST["motivo"] ?? null);
        $idUsuarioQueReporta = $_SESSION["id"];

        if (!$idUsuarioReportado || !$motivo) {
            echo "Error: faltan datos";
            return;
        }

        $this->reportesModel->crearReporteUsuario($idUsuarioQueReporta, $idUsuarioReportado, $motivo);

        $this->redirectModel->redirect('inicio');
    }

    public function banear()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "Falta ID de usuario a banear.";
            exit;
        }

        $this->reportesModel->banearUsuario($id);
        $this->redirectModel->redirect('inicioAdmin');
    }

    public function banearDefinitivamente()
    {
        $id = $_GET["id"] ?? null;

        if (!$id) {
            echo "Falta ID de usuario";
            exit;
        }

        $this->reportesModel->banearDefinitivamente($id);
        $this->redirectModel->redirect('inicioAdmin');
    }


    public function desbanear()
    {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            echo "Falta ID de usuario.";
            exit;
        }

        $this->reportesModel->desbanearUsuario($id);
        $this->redirectModel->redirect('inicioAdmin');
    }

    public function listaBaneados() {
        $reportados = $this->reportesModel->obtenerUsuariosBaneados();

        $this->renderer->render("usuariosReportados", [
            "reportados" => $reportados,
        ]);
    }


}
