<?php
// ==========================================
// AULA 01: O CÓDIGO SPAGHETTI (Tudo misturado)
// ==========================================

// 1. CONEXÃO COM O BANCO DE DADOS E CRIAÇÃO DA TABELA
$dbFile = __DIR__ . '/tasks.sqlite';
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    description TEXT,
    due_date TEXT NOT NULL,
    responsible TEXT NOT NULL,
    done INTEGER DEFAULT 0
)");

// 2. LÓGICA DE NEGÓCIO E CONTROLE DE REQUISIÇÕES
$error = '';

// Criar nova tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = trim($_POST['due_date']);
    $responsible = trim($_POST['responsible']);

    // Validação
    if (empty($title) || empty($due_date) || empty($responsible)) {

        $error = "Título, data de vencimento e responsável são obrigatórios!";

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO tasks (title, description, due_date, responsible)
            VALUES (:title, :description, :due_date, :responsible)
        ");

        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':due_date', $due_date);
        $stmt->bindValue(':responsible', $responsible);

        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}

// Concluir ou excluir tarefa
if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    if ($_GET['action'] === 'complete') {

        $pdo->exec("UPDATE tasks SET done = 1 WHERE id = $id");

    } elseif ($_GET['action'] === 'delete') {

        $pdo->exec("DELETE FROM tasks WHERE id = $id");
    }

    header("Location: index.php");
    exit;
}

// BUSCA DE DADOS
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY id DESC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Master - Spaghetti</title>

    <style>

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            color: #333;
            display: flex;
            justify-content: center;
            padding-top: 50px;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            font-size: 1.5rem;
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .error {
            color: #dc2626;
            background: #fee2e2;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            width: 100%;
        }

        input[type="text"],
        input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px;
            border-bottom: 1px solid #eee;
            gap: 15px;
        }

        li.done {
            opacity: 0.7;
        }

        li.done strong,
        li.done small {
            text-decoration: line-through;
            color: #9ca3af;
        }

        .actions a {
            text-decoration: none;
            margin-left: 10px;
            cursor: pointer;
            font-size: 1.1rem;
        }

    </style>
</head>

<body>

<div class="container">

    <h1>Task Master (Spaghetti Edition)</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">

        <div class="form-group">
            <input type="text" name="title" placeholder="Título da tarefa">
        </div>

        <div class="form-group">
            <input type="text" name="description" placeholder="Descrição da tarefa (opcional)">
        </div>

        <div class="form-group">
            <input type="date" name="due_date">
        </div>

        <div class="form-group">
            <input type="text" name="responsible" placeholder="Responsável pela tarefa">
        </div>

        <button type="submit">Adicionar</button>

    </form>

    <ul>

        <?php foreach ($tasks as $task): ?>

            <li class="<?php echo $task['done'] ? 'done' : ''; ?>">

                <div>

                    <strong>
                        <?php echo htmlspecialchars($task['title']); ?>
                    </strong>

                    <br>

                    <?php if (!empty($task['description'])): ?>

                        <small>
                            <?php echo htmlspecialchars($task['description']); ?>
                        </small>

                        <br>

                    <?php endif; ?>

                    <small>
                        📅 Vencimento:
                        <?php echo htmlspecialchars($task['due_date']); ?>
                    </small>

                    <br>

                    <small>
                        👤 Responsável:
                        <?php echo htmlspecialchars($task['responsible']); ?>
                    </small>

                </div>

                <div class="actions">

                    <?php if (!$task['done']): ?>

                        <a href="?action=complete&id=<?php echo $task['id']; ?>" title="Concluir">
                            ✅
                        </a>

                    <?php endif; ?>

                    <a
                        href="?action=delete&id=<?php echo $task['id']; ?>"
                        onclick="return confirm('Tem certeza que deseja excluir esta tarefa?');"
                        title="Excluir"
                    >
                        ❌
                    </a>

                </div>

            </li>

        <?php endforeach; ?>

    </ul>

</div>

</body>
</html>
