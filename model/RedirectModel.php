<?php

class RedirectModel
{

    public function redirect($vista)
    {
        header("Location: /" . $vista);
        exit;
    }
}