<?php
include_once("helper/MyConexion.php");
include_once("helper/IncludeFileRenderer.php");
include_once("helper/NewRouter.php");
include_once("controller/LoginController.php");
include_once("controller/RegisterController.php");
include_once("controller/InicioController.php");
include_once("model/LoginModel.php");
include_once("model/InicioModel.php");
include_once("model/RedirectModel.php");
include_once("model/RegisterModel.php");
include_once('vendor/mustache/src/Mustache/Autoloader.php');
include_once ("helper/MustacheRenderer.php");
include_once("controller/PaginaPrincipalController.php");

include_once("controller/EditorController.php");
include_once("model/PreguntasModel.php");
include_once("controller/NuevaPreguntaController.php");
include_once("controller/PreguntasListaController.php");
include_once("model/PreguntasModel.php");
include_once("model/RespuestasModel.php");
include_once("controller/GestionarPreguntaController.php");

class ConfigFactory
{
    private $config;
    private $objetos;

    private $conexion;
    private $renderer;
    private $redirectModel;
    private $preguntasModel;
    private $respuestasModel; 

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

        $this->redirectModel = new RedirectModel(); 

        $this->preguntasModel = new PreguntasModel($this->conexion);

        $this->respuestasModel = new RespuestasModel(conexion: $this->conexion);

        $this->objetos["router"] = new NewRouter($this, "LoginController", "base");

        $this->objetos["LoginController"] = new LoginController(new LoginModel($this->conexion), $this->renderer, $this->redirectModel);
    
        $this->objetos["RegisterController"] = new RegisterController(new RegisterModel($this->conexion), $this->renderer, $this->redirectModel);
        
        $this->objetos["InicioController"] = new InicioController(new InicioModel($this->conexion), $this->renderer, $this->redirectModel);

        $this->objetos["PaginaPrincipalController"] = new PaginaPrincipalController(($this->conexion), $this->renderer);
    
        $this->objetos["EditorController"] = new EditorController(($this->conexion), $this->renderer); 
    
        $this->objetos["NuevaPreguntaController"] = new NuevaPreguntaController(($this->conexion), $this->renderer, $this->redirectModel, $this->preguntasModel, $this->respuestasModel);
    
        $this->objetos["PreguntasListaController"] = new PreguntasListaController(($this->conexion), $this->renderer, $this->redirectModel, $this->preguntasModel); 
    
        $this->objetos["GestionarPreguntaController"] = new GestionarPreguntaController(($this->conexion), $this->renderer, $this->redirectModel, $this->preguntasModel, $this->respuestasModel);
    }

    public function get($objectName)
    {
        return $this->objetos[$objectName];
    }
}