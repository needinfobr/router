# Needinfo Router

###### Small, simple and uncomplicated. The router is a PHP route components with abstraction for MVC. Prepared with RESTfull verbs (GET, POST, PUT, PATCH and DELETE), works on its own layer in isolation and can be integrated without secrets to your application.
Pequeno, simples e descomplicado. O router é um componentes de rotas PHP com abstração para MVC. Preparado com verbos RESTfull (GET, POST, PUT, PATCH e DELETE), trabalha em sua própria camada de forma isolada e pode ser integrado sem segredos a sua aplicação.

###### Needinfo is a set of small and optimized PHP components for common tasks. With them you perform routine tasks with fewer lines, writing less and doing much more.
Needinfo é um conjunto de pequenos e otimizados componentes PHP para tarefas comuns. Com eles você executa tarefas rotineiras com poucas linhas, escrevendo menos e fazendo muito mais.

- Router class with all RESTful verbs (Classe router com todos os verbos RESTful)
- Optimized dispatch with total decision control (Despacho otimizado com controle total de decisões)
- It's very simple to create routes for your application or API (É muito simples criar rotas para sua aplicação ou API)
- Trigger and data carrier for the controller (Gatilho e transportador de dados para o controlador)
- Composer ready and PSR-4 compliant (Pronto para o composer e compatível com PSR-4)

## Installation
Router is available via Composer:

```bash
"needinfo/router": "1.0.*"
```

or run

```bash
composer require needinfo/router
```

## Documentation
###### For details on how to use the router, see the sample folder with details in the component directory. To use the router you need to redirect your route routing navigation (index.php) where all traffic must be handled. The example below shows how:
Para mais detalhes sobre como usar o router, veja a pasta de exemplo com detalhes no diretório do componente. Para usar o router é preciso redirecionar sua navegação para o arquivo raiz de rotas (index.php) onde todo o tráfego deve ser tratado. O exemplo abaixo mostra como:

#### Apache

```apache
RewriteEngine On
#Options All -Indexes

## ROUTER URL Rewrite
RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=/$1 [L,QSA]
```

#### Nginx

```nginx
location / {
    if ($script_filename !~ "-f"){
        rewrite ^(.*)$ /index.php?route=/$1 break;
    }
}
```

##### Routes

```php
<?php

use Needinfo\Router\Router;

$router = new Router("https://www.youdomain.com");

/**
 * routes
 * The controller must be in the namespace App\Controllers
 * this produces routes for route, route/$id, etc.
 */
$router->namespace("App\\Controllers");

$router->get("/", "Web:home");
$router->post("/route/{id}", "Web:method");
$router->put("/route/{id}/profile", "Web:method");
$router->patch("/route/{id}/profile/{photo}", "Web:method");
$router->delete("/route/{id}", "Web:method");

/**
 * group by routes and namespace
 * this produces routes for /admin/route and /admin/route/$id
 * The controller must be in the namespace App\Controllers\Admin
 */
$router->group("admin")->namespace("App\\Controllers\\Admin");

$router->get("/route", "Dashboard:home");
$router->post("/route/{id}", "Dashboard:method");

/**
 * This method executes the routes
 */
$router->dispatch();

/*
 * Redirect all errors
 */
if ($router->error()) {
    $router->redirect("/error/{$router->error()}");
}
```

##### Callable

```php
<?php

use Needinfo\Router\Router;

$router = new Router("https://www.youdomain.com");

/**
 * GET httpMethod
 */
$router->get("/callable", function ($data) {
    echo "<h1>GET :: Callable</h1>";
    echo "<pre>", print_r($data, true), "</pre>";
});

/**
 * POST parameterized
 */
$router->post("/callable/{id}", function ($data) {
    echo "<h1>POST :: ID: {$data['id']}</h1>";
});

$router->dispatch();
```

## Contributing
Please see [CONTRIBUTING](https://github.com/needinfobr/router/blob/master/CONTRIBUTING.md) for details.

## Support
###### Security: If you discover any security related issues, please email [contato@needinfo.com.br](mailto:contato@needinfo.com.br) instead of using the issue tracker.
Se você descobrir algum problema relacionado à segurança, envie um e-mail para [contato@needinfo.com.br](mailto:contato@needinfo.com.br) em vez de usar o rastreador de problemas.

## License
The MIT License (MIT).
