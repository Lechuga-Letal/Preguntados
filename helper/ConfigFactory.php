<?php
include_once("helper/MyConexion.php");
include_once("helper/IncludeFileRenderer.php");
include_once("helper/NewRouter.php");
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
class ConfigFactory
{
    private $config;
    private $objetos;

    private $conexion;
    private $renderer;
    private $redirectModel;

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

        $this->objetos["router"] = new NewRouter($this, "LoginController", "base");

        $this->objetos["LoginController"] = new LoginController(new UsuarioModel($this->conexion), $this->renderer, $this->redirectModel);
    
        $this->objetos["RegisterController"] = new RegisterController(new UsuarioModel($this->conexion), $this->renderer, $this->redirectModel);
        
        $this->objetos["InicioController"] = new InicioController(new InicioModel($this->conexion), $this->renderer, $this->redirectModel);

        $this->objetos["PaginaPrincipalController"] = new PaginaPrincipalController(($this->conexion), $this->renderer);

        $this->objetos["InicioAdminController"] = new InicioAdminController(new InicioAdminModel($this->conexion), $this->renderer);

        $this->objetos["MapaController"] = new MapaController();

    }

    public function get($objectName)
    {
        return $this->objetos[$objectName];
    }
}