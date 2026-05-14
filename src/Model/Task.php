<?php
// ==========================================
// MODEL - RESPONSÁVEL PELOS DADOS DAS TAREFAS
// ==========================================

class Task
{
    // Guarda a conexão com o banco de dados.
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        // Garante que o banco esteja pronto para uso.
        $this->createTableIfNotExists();
        $this->ensureResponsibleColumnExists();
    }

    // Cria a tabela de tarefas caso ela ainda não exista.
    private function createTableIfNotExists(): void
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            due_date TEXT NOT NULL,
            responsible TEXT NOT NULL DEFAULT 'Não informado',
            done INTEGER DEFAULT 0
        )");
    }

    // Mantém compatibilidade caso o banco antigo ainda não tenha a coluna responsible.
    private function ensureResponsibleColumnExists(): void
    {
        $columns = $this->pdo->query("PRAGMA table_info(tasks)")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'name');

        if (!in_array('responsible', $columnNames, true)) {
            $this->pdo->exec("ALTER TABLE tasks ADD COLUMN responsible TEXT NOT NULL DEFAULT 'Não informado'");
        }
    }

    // Busca todas as tarefas cadastradas.
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM tasks ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Salva uma nova tarefa depois das validações.
    public function save(string $title, string $description, string $dueDate, ?string $responsible = null): bool
    {
        // Limpa espaços extras enviados pelo formulário.
        $title = trim($title);
        $description = trim($description);
        $dueDate = trim($dueDate);
        $responsible = trim((string) $responsible);

        // Valida campos obrigatórios.
        if ($title === '' || $dueDate === '') {
            throw new Exception("Título e Data são obrigatórios.");
        }

        if ($responsible === '') {
            throw new Exception("Responsável é obrigatório.");
        }

        // Impede criação de tarefas com data vencida.
        if ($dueDate < date('Y-m-d')) {
            throw new Exception("Não é permitido criar tarefas com data vencida!");
        }

        // Insere no banco usando prepared statement.
        $stmt = $this->pdo->prepare(
            "INSERT INTO tasks (title, description, due_date, responsible)
             VALUES (:title, :description, :due_date, :responsible)"
        );

        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':due_date', $dueDate);
        $stmt->bindValue(':responsible', $responsible);

        return $stmt->execute();
    }

    // Marca uma tarefa como concluída.
    public function complete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE tasks SET done = 1 WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Exclui uma tarefa pelo ID.
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
