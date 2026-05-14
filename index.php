<?php
// ==========================================
// TASK MASTER - FRONT CONTROLLER MVC
// ==========================================

// Autoload simples: procura as classes nas pastas do MVC.
spl_autoload_register(function (string $class): void {
    $dirs = ['Model', 'Controller', 'View'];

    foreach ($dirs as $dir) {
        $file = __DIR__ . "/src/$dir/$class.php";

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Conexão única com o banco de dados SQLite.
$pdo = new PDO('sqlite:' . __DIR__ . '/tasks.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Cria o Controller principal do sistema.
$controller = new TaskController($pdo);

// Define a action pela URL. Exemplo: index.php?action=create
$action = $_GET['action'] ?? 'index';

// Executa a action se ela existir no Controller.
if (method_exists($controller, $action)) {
    $controller->$action();
    exit;
}

// Caso a action não exista, retorna erro simples.
http_response_code(404);
echo 'Página não encontrada 404';
