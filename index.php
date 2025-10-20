<?php
session_start();

include("helper/ConfigFactory.php");

$configFactory = new ConfigFactory();
$router = $configFactory->get("router");

$controllerParam = $_GET["controller"] ?? null;
$methodParam = $_GET["method"] ?? null;

$router->executeController($controllerParam, $methodParam);
