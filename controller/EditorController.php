<?php

class EditorController
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
        $this->editor();
    }

    public function editor()
    {
        $this->renderer->render("editor");
    }

}
