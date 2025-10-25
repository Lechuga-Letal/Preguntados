<?php

class MapaController
{

    public function __construct()
    {

    }

    public function base()
    {
        $this->obtenerCiudadYPais();
    }

    public function obtenerCiudadYPais(){

        $data = json_decode(file_get_contents('php://input'), true);
        $lat = $data['lat'] ?? null;
        $lng = $data['lng'] ?? null;

        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&zoom=10&addressdetails=1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MapaPHP/1.0');
        $response = curl_exec($ch);
        curl_close($ch);

        $resultado = json_decode($response, true);
        $pais = $resultado['address']['country'];
        $provincia = $resultado['address']['state'];


        echo json_encode([
            'provincia' => $provincia, 
            'pais' => $pais
        ]);
    }
}
