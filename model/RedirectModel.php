<?php

class RedirectModel
{

    public function redirect($vista)
    {
        header("Location: /" . $vista);
        exit;
    }

    public function redirectConVariable($vista, $id)
    {
        $_SESSION['busqueda'] = $id;
        header("Location: /" . $vista);
        exit;
    }

    /*
    public function deshacerVariable()
    {
        unset($_SESSION['busqueda']);
    } */
}