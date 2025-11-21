<?php
require_once __DIR__ . '/../helper/MailService.php';
 class UsuarioModel{

    private $conexion; 

     public function __construct($conexion)
     {
         $this->conexion = $conexion;
     }

     public function userExists($usuario, $mail)
     {
         $sql = "SELECT id FROM usuarios WHERE usuario = '$usuario' OR mail = '$mail'";
         $result = $this->conexion->query($sql);

         return !empty($result) && count($result) > 0;
     }

     public function createUser($nombre_completo, $anio_nacimiento, $sexo, $pais, $ciudad, $coordenadas, $usuario, $mail, $pass1, $foto_perfil, $rol = 'Jugador')
     {
         $hashed = password_hash($pass1, PASSWORD_DEFAULT);

         $sql = "INSERT INTO usuarios 
        (nombre_completo, anio_nacimiento, sexo, pais, ciudad, coordenadas, usuario, mail, password, foto_perfil, rol) 
        VALUES ('$nombre_completo', '$anio_nacimiento', '$sexo', '$pais', '$ciudad', '$coordenadas', '$usuario', '$mail', '$hashed', '$foto_perfil', '$rol')";

         $success = $this->conexion->query($sql);

         $mailService = new MailService();
         $mailService->enviarBienvenida($usuario, $mail);

         $idUsuario= $this->obtenerIdUsuarioPorNombre($usuario);
         $this->creacionDeNivelDeUsuario($idUsuario);

         return $success;
     }

     public function creacionDeNivelDeUsuario($idUsuario){

         $categorias=$this->obtenerCategorias();
         for ($i=0; $i < count($categorias); $i++) {
             $idCategoria = $categorias[$i]['id_categoria'];
             $query = "INSERT INTO nivelJugadorPorCategoria (id_usuario, id_categoria)
                       VALUES ('$idUsuario', '$idCategoria')";
             $this->conexion->query($query);
         }

         $query="INSERT INTO nivelJugadorGeneral (id_usuario)
                     VALUES ('$idUsuario')";
         $this->conexion->query($query);
     }
     public function obtenerCategorias(){
         $query = "select id_categoria from categoria";
         return $this->conexion->query($query);
     }

     public function getUserWith($user, $password)
     {
        $sql = "SELECT id, usuario, password, rol, sexo, foto_perfil FROM usuarios WHERE usuario = '$user'";

         $result = $this->conexion->query($sql);


         if (!$result || count($result) === 0) {
             return [];
         }

         $userRow = $result[0];

         if (password_verify($password, $userRow['password'])) {
             return [
                 'id' => $userRow['id'],
                 'usuario' => $userRow['usuario'],
                 'rol' => $userRow['rol'],
                 'sexo' => $userRow['sexo'],
                 'foto_perfil' => $userRow['foto_perfil']
             ];
         }

         return [];
     }

    public function getUsuarioById($id)
    {
        if (!is_numeric($id)) {
            return null;
        }

        $sql = "SELECT * FROM usuarios WHERE id = $id";
        $resultado = $this->conexion->query($sql);
        if ($resultado && count($resultado) > 0) {
            return (array) $resultado[0];
        }

        return null;
    }

    public function getNivelUsuarioPorCategoria($idUsuario,$idCategoria){
        $sql = "SELECT nivel FROM niveljugadorporcategoria
                WHERE id_usuario = '$idUsuario'
                and id_categoria = '$idCategoria'";
        $resultado = $this->conexion->query($sql);

        return $resultado[0]["nivel"];
    }

    public function getAllUsuarios()
    {
        $sql = "SELECT * FROM usuarios WHERE rol <> 'Administrador'";
        $resultado = $this->conexion->query($sql);

        if ($resultado && count($resultado) > 0) {
            return $resultado;
        }
        return [];
    }

        public function obtenerIdUsuarioPorNombre($nombreUsuario)
    {
        $sql = "SELECT id FROM usuarios WHERE usuario = '$nombreUsuario'";
        //Agrege el @ para que el warning no aparecza. 
        //Pero deberiamos preguntar por el foro porque a veces nos sale y a veces no!
        $resultado = $this->conexion->query($sql);

        if ($resultado && count($resultado) > 0) {
            return $resultado[0]['id'];
        }

        return null;
    }

    public function obtenerListadoDeJugadoresMenos($idActual)
    {

        if (!is_numeric($idActual)) {
            $idUsuario = $this->obtenerIdUsuarioPorNombre($idActual);
        } else {
            $idUsuario = $idActual;
        }

        $query = "SELECT foto_perfil, id, usuario, nombre_completo, pais FROM usuarios 
        WHERE id != $idUsuario AND usuario != 'admin'";

        $resultado = @$this->conexion->query($query);

        if (is_array($resultado)) {
            return $resultado;
        }

        $usuarios = [];
        while ($fila = $resultado->fetch_assoc()) {
            $usuarios[] = $fila;
        }

        return $usuarios;
    }

    public function getUsuarioByNombreUsuario($nombre)
    {
    if ($nombre === null || $nombre === '') {
        return null;
    }

    $sql = "SELECT * FROM usuarios WHERE usuario = '$nombre' LIMIT 1";
    $resultado = $this->conexion->query($sql);

    if (is_array($resultado) && count($resultado) > 0) {
        return $resultado[0];
    }

    if (is_object($resultado) && property_exists($resultado, 'num_rows')) {
        if ($resultado->num_rows > 0) {
            return $resultado->fetch_assoc();
        }
    }

    return null;
    }

     public function mensajeDeRevisionDeErrores(){
         var_dump("llegue");
         die();
     }

     //aca voy a hacer las consultas sobre lo de ranking pero no se si seria mejor ponerlo en otro modelo
    public function obtenerListaMejoresJugadores()
    //generico sin filtros de nada (a mejorar)
    {
        //de usuario sacamos id, nombre completo, pais, foto(?), rol
        //de partida sacamos id_usuario, puntaje, estado(?)

        $sql = "SELECT u.id, u.nombre_completo, u.pais, max(part.puntaje) AS mejor_puntaje
                FROM partidas part
                JOIN usuarios u ON part.id_usuario = u.id
                WHERE part.estado = 'finalizada' 
                GROUP BY u.id
                ORDER BY mejor_puntaje DESC" ;  

        $resultado = $this->conexion->query($sql);
        if ($resultado && count($resultado) > 0) {
            return $resultado;
        }
        return [];
     }


    public function obtenerListaMejoresJugadoresPorRango($rango, $limite){

        switch ($rango) {
            case 'pro':
                $nivel = "nivelJ.nivel >= 0.7";
                break;
            case 'medio':
                $nivel = "nivelJ.nivel >= 0.4 AND nivelJ.nivel < 0.7";
                break;
            case 'novato':
                $nivel = "nivelJ.nivel < 0.3";
                break;
            default:
                return [];
        }


        $sql = "SELECT u.id, u.foto_perfil, u.nombre_completo, u.pais, max(part.puntaje) AS mejor_puntaje
                FROM partidas part
                JOIN usuarios u ON part.id_usuario = u.id
                JOIN nivelJugadorGeneral nivelJ ON nivelJ.id_usuario = u.id
                WHERE part.estado = 'finalizada' 
                AND $nivel
                GROUP BY u.id
                ORDER BY mejor_puntaje DESC
                LIMIT $limite"; 

        $resultado = $this->conexion->query($sql);
        if ($resultado && count($resultado) > 0) {
            return $resultado;
        }
        return [];
     }

    public function obtenerListaMejoresJugadoresPorCategoria($categoria, $limite){
        $categoriaSeleccionada = '';
        switch($categoria){
            case 'Deportes':
                $categoriaSeleccionada = 1;
                break;
            case 'Entretenimiento':
                $categoriaSeleccionada = 2;
                break;
            case 'Informática':
                $categoriaSeleccionada = 3;
                break;
            case 'Matematicas':
                $categoriaSeleccionada = 4;
                break;
            case 'Historia':
                $categoriaSeleccionada = 5;
                break;
            default:
                return [];

        }


        $sql = "SELECT u.id, u.foto_perfil, u.nombre_completo, u.pais, nivelCate.nivel AS mejor_puntaje
                FROM usuarios u
                JOIN niveljugadorporcategoria nivelCate ON nivelCate.id_usuario = u.id
                WHERE nivelCate.id_categoria = $categoriaSeleccionada
                ORDER BY mejor_puntaje DESC
                LIMIT $limite"; 

        $resultado = $this->conexion->query($sql);
        if ($resultado && count($resultado) > 0) {
            return $resultado;
        }
        return [];
     }


 }