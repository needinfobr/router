# Needinfo Router

###### Small, simple and uncomplicated. The router is a PHP route components with abstraction for MVC. Prepared with RESTfull verbs (GET, POST, PUT, PATCH and DELETE), works on its own layer in isolation and can be integrated without secrets to your application.
Pequeno, simples e descomplicado. O router é um componentes de rotas PHP com abstração para MVC. Preparado com verbos RESTfull (GET, POST, PUT, PATCH e DELETE), trabalha em sua própria camada de forma isolada e pode ser integrado sem segredos a sua aplicação.

###### Needinfo is a set of small and optimized PHP components for common tasks. With them you perform routine tasks with fewer lines, writing less and doing much more.
Needinfo é um conjunto de pequenos e otimizados componentes PHP para tarefas comuns. Com eles você executa tarefas rotineiras com poucas linhas, escrevendo menos e fazendo muito mais.

- Router class with all RESTful verbs (Classe router com todos os verbos RESTful)
- **[NEW no v2.0]** Regex Constraints nos parâmetros de URL
- **[NEW no v2.0]** Retorno `405 Method Not Allowed` estruturado (+ Allow Headers)
- **[NEW no v2.0]** Encadeamento de Rotas (`->name()`, `->middleware()`, `->with()`)
- **[NEW no v2.0]** Matcher Desacoplado para uso focado em Frameworks que precisam ingerir uma Request customizada (Injeção de PSR-11 support).
- Optimized dispatch with total decision control (Despacho otimizado com controle total de decisões)
- Composer ready and PSR-4 compliant (Pronto para o composer e compatível com PSR-4)

## Installation
Router is available via Composer:

```bash
composer require needinfobr/router "^2.0"
```

## Documentation
###### For details on how to use the router, see the sample folder with details in the component directory. To use the router you need to redirect your route routing navigation (index.php) where all traffic must be handled. The example below shows how:
Para mais detalhes sobre como usar o router, veja a pasta de exemplo com detalhes no diretório do componente. Para usar o router é preciso redirecionar sua navegação para o arquivo raiz de rotas (index.php) onde todo o tráfego deve ser tratado.

#### Apache (`.htaccess`)
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

### 1. Basic usage and MVC Pattern

```php
<?php

use Needinfo\Router\Router;

$router = new Router("https://www.youdomain.com");

/**
 * The controller must be in the namespace App\Controllers
 */
$router->namespace("App\\Controllers");

$router->get("/", "Web:home");
// Default parameters catch anything [^/]+
$router->post("/route/{id}", "Web:method");

// You can use Regex Constraints (v2.0+) 
$router->get("/users/{id:\d+}", "Web:user"); // id only matches Numbers!
$router->get("/posts/{slug:[a-z\-]+}", "Web:post"); // slug only matches lowercase and hyphens!

/**
 * Group by routes and namespace
 * The controller must be in the namespace App\Controllers\Admin
 */
$router->group("admin")->namespace("App\\Controllers\\Admin");

$router->get("/route", "Dashboard:home");

/**
 * This method executes the routes directly based on $_SERVER
 */
$router->dispatch();

/*
 * Redirect all errors
 */
if ($router->error()) {
    $router->redirect("/error/{$router->error()}"); // e.g., 404 or 405
}
```

### 2. Route Decorators (v2.0+)
Agora toda chamada de rota retorna o objeto `Needinfo\Router\Route`, que permite você injetar dados avançados usados pelo seu sistema MVC:

```php
$route = $router->get("/dashboard", "Web:dashboard")
                ->name("admin.dashboard")
                ->middleware("RequireAuth")
                ->with(["role" => "admin"]);
```

### 3. Framework Integrations e Dependency Injection (v2.0+)

#### Injeção Container Simples
Se seu index for impulsionado por um container, basta setá-lo no Roteador antes do dispatcher. Se houver métodos como `has` e `get` (PSR-11), o Router fará `->get('App\Controllers\Web')` garantindo autowiring!

```php
$router->setContainer($myContainer);
$router->dispatch();
```

#### Passagem de Contexto (Context Injection)
Muitos frameworks enviam o `Request` para o Controller ou Callable. Agora você pode passar na chamada de `$router->dispatch($context)` e recuperar lá dentro:

```php
$router->get("/api/user", function($request, $params) {
    echo $request->getBody(); // $request é o contexto customizado repassado
});

$router->dispatch($request);
```

#### Matcher Desacoplado 
Não quer rodar a lógica `new Controller` direto do core do Router? Quer varrer e ver se algo casou apenas? O comando `match()` responde uma Query estruturada!

```php
$match = $router->match('POST', '/api/users');

if ($match->isSuccess()) {
    $matchedRoute = $match->getRoute();
    $params = $match->getParams();
    
    // Voce assume o controle da execução:
    // (new $matchedRoute->getHandler())($params);
} elseif ($match->getError() === 405) {
    echo "Metodos aceitos: " . implode(', ', $match->getAllowedMethods());
} else {
    echo "404 Not Found";
}
```

## Contributing
Please see [CONTRIBUTING](https://github.com/needinfobr/router/blob/master/CONTRIBUTING.md) for details.

## Support
Security: If you discover any security related issues, please email [contato@needinfo.com.br](mailto:contato@needinfo.com.br) instead of using the issue tracker.

## License
The MIT License (MIT).
