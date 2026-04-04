<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Controllers/Web.php';

use Needinfo\Router\Router;

// Instancia o roteador (Assumindo servidor embutido rodando na pasta base)
$router = new Router("http://localhost:8000");

// Define o namespace
$router->namespace("Example\\Controllers");

// Rotas
$router->get("/", "Web:home");
$router->get("/sobre", "Web:about");
$router->get("/contato/{id}", "Web:contact");

// Dispatch
$router->dispatch();

if ($router->error()) {
    echo "<h1>Erro {$router->error()}</h1>";
}
