<?php
// ==========================================
// PRESENTER - CENTRALIZA A LÓGICA DE APRESENTAÇÃO
// ==========================================

class TaskPresenter {
    // Model usado para acessar e alterar as tarefas.
    private $model;

    // View usada para exibir os dados já preparados pelo Presenter.
    private $view;

    // INJEÇÃO DE DEPENDÊNCIA: O Presenter exige o Model e a INTERFACE da View
    public function __construct(Task $model, TaskViewInterface $view) {
        $this->model = $model;
        $this->view = $view;
    }

    // Exibe a página inicial com a lista de tarefas.
    public function index() {
        try {
            $tasks = $this->model->getAll();

            // Lógica de Formatação da Apresentação (Apenas no MVP)
            foreach ($tasks as &$task) {
                $task['title'] = strtoupper($task['title']);
            }
            unset($task);

            // O Presenter manda a View exibir as tarefas
            $this->view->displayTasks($tasks);
        } catch (Exception $e) {
            // Em caso de erro, manda a View exibir o erro
            $this->view->showError($e->getMessage());
        }
    }

    // Recebe o formulário e cria uma nova tarefa.
    public function create($title, $description, $dueDate, $responsible = '') {
        try {
            $this->model->save($title, $description, $dueDate, $responsible);

            // Redireciona para evitar reenvio do formulário ao atualizar a página.
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            // Se houver erro de validação, mostra a mensagem na tela.
            $this->view->showError($e->getMessage());
        }
    }

    // Marca uma tarefa como concluída.
    public function complete($id) {
        $this->model->complete((int) $id);
        header("Location: index.php");
        exit;
    }

    // Exclui uma tarefa.
    public function delete($id) {
        $this->model->delete((int) $id);
        header("Location: index.php");
        exit;
    }
}
?>
