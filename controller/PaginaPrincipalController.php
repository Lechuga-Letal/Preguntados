<?php
class PaginaPrincipalController
{
    private $model;
    private $renderer;

    public function __construct($model, $renderer)
    {
        $this->model = $model;
        $this->renderer = $renderer;
    }

    public function base()
    {
        $this->MostrarpaginaPrincipal();
    }

    public function mostrarPaginaPrincipal()
    {
        $this->renderer->render("paginaPrincipal");
    }
}