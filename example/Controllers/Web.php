<?php

namespace Example\Controllers;

class Web
{
    public function home(): void
    {
        echo "<h1>Home Page</h1>";
        echo "<p>Bem-vindo ao roteador super simples e robusto!</p>";
        echo "<p><a href='/sobre'>Ir para sobre</a></p>";
        echo "<p><a href='/contato/123'>Ir para contato com ID</a></p>";
    }

    public function about(): void
    {
        echo "<h1>Sobre</h1>";
        echo "<p>Página sobre o projeto.</p>";
        echo "<p><a href='/'>Voltar para Home</a></p>";
    }

    public function contact(array $data): void
    {
        echo "<h1>Contato</h1>";
        echo "<p>ID recebido via URL: " . htmlspecialchars($data['id']) . "</p>";
        echo "<p><a href='/'>Voltar para Home</a></p>";
    }
}
