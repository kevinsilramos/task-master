<?php
// ==========================================
// CÓDIGO SPAGHETTI POR KEVIN SILVA
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

// 2. LÓGICA DE NEGÓCIO
$error = '';

// Criar tarefa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = trim($_POST['due_date']);
    $responsible = trim($_POST['responsible']);

    if (empty($title) || empty($due_date) || empty($responsible)) {

        $error = "Título, data de vencimento e responsável são obrigatórios!";

    } elseif ($due_date < date('Y-m-d')) {

        $error = "Não é permitido criar tarefas com data vencida!";

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

// Concluir ou excluir
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

// Buscar tarefas
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY id DESC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Task Master - Conecta Fatec</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {

            font-family: 'Inter', sans-serif;

            background: linear-gradient(
                135deg,
                #002fff,
                #000f33
            );

            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 40px 20px;

            color: #ffffff;
        }

        .container {

            width: 100%;
            max-width: 650px;

            background: #ffffff;

            border-radius: 18px;

            padding: 30px;

            box-shadow:
                0 10px 30px rgba(0,0,0,0.25);

            animation: fadeIn 0.4s ease;
        }

        h1 {

            color: #0f172a;

            font-size: 2rem;

            font-weight: 800;

            margin-bottom: 25px;

            text-align: center;
        }

        .error {

            background: #fee2e2;

            color: #b91c1c;

            padding: 12px;

            border-radius: 10px;

            margin-bottom: 15px;

            font-size: 0.9rem;
        }

        form {

            display: flex;
            flex-direction: column;

            gap: 12px;

            margin-bottom: 25px;
        }

        input {

            width: 100%;

            padding: 14px;

            border-radius: 10px;

            border: 1px solid #dbe1ea;

            font-family: 'Inter', sans-serif;

            font-size: 0.95rem;

            transition: 0.2s ease;
        }

        input:focus {

            outline: none;

            border-color: #2563eb;

            transform: translateY(-1px);
        }

        button {

            background: #2563eb;

            color: white;

            border: none;

            padding: 14px;

            border-radius: 10px;

            font-family: 'Inter', sans-serif;

            font-weight: 600;

            cursor: pointer;

            transition: 0.2s ease;

            box-shadow: 0 6px 15px rgba(37,99,235,0.25);
        }

        button:hover {

            background: #1d4ed8;

            transform: translateY(-2px);
        }

        ul {

            list-style: none;

            display: flex;
            flex-direction: column;

            gap: 15px;
        }

        li {

            background: #f8fafc;

            border-radius: 14px;

            padding: 18px;

            display: flex;

            justify-content: space-between;

            gap: 15px;

            box-shadow:
                0 4px 12px rgba(0,0,0,0.06);

            transition: 0.2s ease;
        }

        li:hover {

            transform: translateY(-2px);
        }

        li.done {

            opacity: 0.7;
        }

        li.done strong,
        li.done small {

            text-decoration: line-through;
        }

        strong {

            color: #0f172a;

            font-size: 1rem;

            font-weight: 700;
        }

        small {

            display: block;

            margin-top: 6px;

            color: #475569;

            line-height: 1.5;
        }

        .actions {

            display: flex;

            align-items: flex-start;

            gap: 10px;
        }

        .actions a {

            text-decoration: none;

            font-size: 1.1rem;

            transition: 0.2s ease;
        }

        .actions a:hover {

            transform: scale(1.15);
        }

        @keyframes fadeIn {

            from {

                opacity: 0;
                transform: translateY(10px);
            }

            to {

                opacity: 1;
                transform: translateY(0);
            }
        }

        .task-header {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 5px;
        }

        .expired-badge {

            background: #dc2626;

            color: white;

            font-size: 0.75rem;

            font-weight: 600;

            padding: 4px 8px;

            border-radius: 999px;
        }

        li.expired {

            border-left: 4px solid #dc2626;
        }

    </style>

</head>

<body>

    <div class="container">

        <h1>Task Master</h1>

        <?php if ($error): ?>

            <div class="error">
                <?php echo $error; ?>
            </div>

        <?php endif; ?>

        <form method="POST" action="index.php">

            <input
                type="text"
                name="title"
                placeholder="Título da tarefa"
            >

            <input
                type="text"
                name="description"
                placeholder="Descrição da tarefa (opcional)"
            >

            <input
                type="date"
                name="due_date"
            >

            <input
                type="text"
                name="responsible"
                placeholder="Responsável pela tarefa"
            >

            <button type="submit">
                Adicionar tarefa
            </button>

        </form>

        <ul>

            <?php foreach ($tasks as $task): ?>

                <?php
                    $isExpired =
                        !$task['done'] &&
                        $task['due_date'] < date('Y-m-d');
                ?>

                <li class="
                    <?php echo $task['done'] ? 'done' : ''; ?>
                    <?php echo $isExpired ? 'expired' : ''; ?>
                ">

                    <div>

                        <div class="task-header">

                            <strong>
                                <?php echo htmlspecialchars($task['title']); ?>
                            </strong>

                            <?php if ($isExpired): ?>

                                <span class="expired-badge">
                                    Vencido
                                </span>

                            <?php endif; ?>

                        </div>

                        <?php if (!empty($task['description'])): ?>

                            <small>
                                <?php echo htmlspecialchars($task['description']); ?>
                            </small>

                        <?php endif; ?>

                        <small>
                            📅 Vencimento:
                            <?php echo htmlspecialchars($task['due_date']); ?>
                        </small>

                        <small>
                            👤 Responsável:
                            <?php echo htmlspecialchars($task['responsible']); ?>
                        </small>

                    </div>

                    <div class="actions">

                        <?php if (!$task['done']): ?>

                            <a
                                href="?action=complete&id=<?php echo $task['id']; ?>"
                                title="Concluir"
                            >
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