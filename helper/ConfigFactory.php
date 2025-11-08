<?php
include_once("helper/MyConexion.php");
include_once("helper/IncludeFileRenderer.php");
include_once("helper/NewRouter.php");
include_once("helper/MailService.php");
include_once("controller/LoginController.php");
include_once("controller/RegisterController.php");
include_once("controller/InicioController.php");
include_once("model/UsuarioModel.php");
include_once("model/InicioModel.php");
include_once("model/RedirectModel.php");
include_once('vendor/mustache/src/Mustache/Autoloader.php');
include_once ("helper/MustacheRenderer.php");
include_once("controller/PaginaPrincipalController.php");
include_once("controller/MapaController.php");
include_once("controller/InicioAdminController.php");
include_once("model/InicioAdminModel.php");

include_once("controller/InicioEditorController.php");
include_once("model/PreguntasModel.php");
include_once("controller/NuevaPreguntaController.php");
include_once("controller/PreguntasListaController.php");
include_once("model/PreguntasModel.php");
include_once("model/RespuestasModel.php");
include_once("controller/GestionarPreguntaController.php");

include_once("controller/PartidaController.php");
include_once("controller/ReportarPreguntaController.php");
include_once("model/PartidaModel.php");

include_once("controller/RankingController.php");
include_once("controller/perfilController.php");

include_once("model/ReportesModel.php");
class ConfigFactory
{
    private $config;
    private $objetos;

    private $conexion;
    private $renderer;
    private $redirectModel;
    private $preguntasModel;
    private $respuestasModel;
    private $usuarioModel; 
    private $mailService;
    private $reportesModel;
    public function __construct()
    {
        $this->config = parse_ini_file("config/config.ini");

        $this->conexion= new MyConexion(
            $this->config["server"],
            $this->config["user"],
            $this->config["pass"],
            $this->config["database"]
        );
        $this->renderer = new MustacheRenderer("vista");

        $this->mailService = new MailService(); 

        $this->redirectModel = new RedirectModel(); 

        $this->reportesModel = new ReportesModel($this->conexion);

        $this->objetos["router"] = new NewRouter($this, "PaginaPrincipalController", "base");
        $this->preguntasModel = new PreguntasModel($this->conexion);

        $this->respuestasModel = new RespuestasModel(conexion: $this->conexion);

        $this->usuarioModel = new UsuarioModel($this->conexion);

        $this->objetos["router"] = new NewRouter($this, "PaginaPrincipalController", "base");

//Hay 2 instancias de modelo, este se tiene que crear previamente e inyectarlo en los controladores que se necesita
        $this->objetos["LoginController"] = new LoginController(new UsuarioModel($this->conexion), $this->renderer, $this->redirectModel);
    
        $this->objetos["RegisterController"] = new RegisterController(new UsuarioModel($this->conexion), $this->renderer, $this->redirectModel, $this->mailService);
        
        $this->objetos["InicioController"] = new InicioController(new InicioModel($this->conexion), $this->renderer, $this->redirectModel);

        $this->objetos["PaginaPrincipalController"] = new PaginaPrincipalController(($this->conexion), $this->renderer);

        $this->objetos["InicioAdminController"] = new InicioAdminController(new InicioAdminModel($this->conexion), $this->renderer);

        $this->objetos["PartidaController"]= new PartidaController(new PartidaModel($this->conexion, $this->usuarioModel), $this->renderer, $this->redirectModel, $this->usuarioModel);

        $this->objetos["MapaController"] = new MapaController();

        $this->objetos["InicioEditorController"] = new InicioEditorController(($this->conexion), $this->renderer);

        $this->objetos["NuevaPreguntaController"] = new NuevaPreguntaController(($this->conexion), $this->renderer, $this->redirectModel, $this->preguntasModel, $this->respuestasModel, $this->usuarioModel);

        $this->objetos["PreguntasListaController"] = new PreguntasListaController(($this->conexion), $this->renderer, $this->redirectModel, $this->preguntasModel);

        $this->objetos["GestionarPreguntaController"] = new GestionarPreguntaController(($this->conexion), $this->renderer, $this->redirectModel, $this->preguntasModel, $this->respuestasModel, $this->reportesModel);
    
        $this->objetos["RankingController"] = new RankingController(($this->conexion), $this->renderer, $this->redirectModel, $this->usuarioModel);
    
        $this->objetos["PerfilController"] = new PerfilController(($this->conexion), $this->renderer, $this->redirectModel, $this->usuarioModel);
    
        $this->objetos["ReportarPreguntaController"] = new ReportarPreguntaController(($this->conexion), $this->renderer, $this->redirectModel, $this->usuarioModel, $this->preguntasModel, $this->respuestasModel, $this->reportesModel);
    }

    public function get($objectName)
    {
        return $this->objetos[$objectName];
    }
}