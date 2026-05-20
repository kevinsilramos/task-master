<?php
// ==========================================
// TASK MASTER - FRONT CONTROLLER MVP
// ==========================================

// Autoload simples: procura as classes nas pastas do MVP.
// Atualizamos o Autoload para incluir a pasta Presenter.
spl_autoload_register(function (string $class): void {
    $dirs = ['Model', 'Presenter', 'View'];

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

// 1. Instanciamos o Model.
$model = new Task($pdo);

// 2. Instanciamos a View (que implementa TaskViewInterface).
$view = new TaskHtmlView();

// 3. Instanciamos o Presenter, injetando as dependências.
$presenter = new TaskPresenter($model, $view);

// Define a action pela URL. Exemplo: index.php?action=create
$action = $_GET['action'] ?? 'index';

// Roteamento
if ($action === 'create') {
    $presenter->create(
        $_POST['title'] ?? '',
        $_POST['description'] ?? '',
        $_POST['due_date'] ?? '',
        $_POST['responsible'] ?? ''
    );
} elseif ($action === 'complete') {
    $presenter->complete($_GET['id'] ?? 0);
} elseif ($action === 'delete') {
    $presenter->delete($_GET['id'] ?? 0);
} else {
    $presenter->index();
}
?>
