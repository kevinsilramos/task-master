<?php
// ==========================================
// CONTROLLER - FAZ A PONTE ENTRE MODEL E VIEW
// ==========================================

class TaskController
{
    // Model usado para acessar e alterar as tarefas.
    private Task $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new Task($pdo);
    }

    // Exibe a página inicial com a lista de tarefas.
    public function index(?string $error = null): void
    {
        $tasks = $this->model->getAll();
        require __DIR__ . '/../View/list.php';
    }

    // Recebe o formulário e cria uma nova tarefa.
    public function create(): void
    {
        try {
            $this->model->save(
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['due_date'] ?? '',
                $_POST['responsible'] ?? ''
            );

            // Redireciona para evitar reenvio do formulário ao atualizar a página.
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            // Se houver erro de validação, mostra a mensagem na tela.
            $this->index($e->getMessage());
        }
    }

    // Marca uma tarefa como concluída.
    public function complete(): void
    {
        $this->model->complete((int) ($_GET['id'] ?? 0));
        header('Location: index.php');
        exit;
    }

    // Exclui uma tarefa.
    public function delete(): void
    {
        $this->model->delete((int) ($_GET['id'] ?? 0));
        header('Location: index.php');
        exit;
    }
}
