<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Task Master - Conecta Fatec</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">

    <style>
        /* Reset básico da página */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Fundo e centralização geral */
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #002fff, #000f33);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            color: #ffffff;
        }

        /* Card principal */
        .container {
            width: 100%;
            max-width: 650px;
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            animation: fadeIn 0.4s ease;
        }

        h1 {
            color: #0f172a;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 25px;
            text-align: center;
        }

        /* Mensagem de erro enviada pelo Controller */
        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        /* Formulário de criação de tarefas */
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

        /* Lista de tarefas */
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
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

        /* Botões de ação da tarefa */
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

        .task-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 5px;
        }

        /* Destaque visual para tarefa vencida */
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
    </style>

</head>

<body>

    <!-- A View recebe os dados prontos e apenas desenha a tela. -->
    <div class="container">

        <h1>Task Master (MVC Edition)</h1>

        <!-- Mostra erro de validação, se o Controller enviar. -->
        <?php if (!empty($error)): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário enviado para a action create. -->
        <form method="POST" action="index.php?action=create">

            <input
                type="text"
                name="title"
                placeholder="Título da tarefa"
                required
            >

            <input
                type="text"
                name="description"
                placeholder="Descrição da tarefa (opcional)"
            >

            <input
                type="date"
                name="due_date"
                required
            >

            <input
                type="text"
                name="responsible"
                placeholder="Responsável pela tarefa"
                required
            >

            <button type="submit">
                Adicionar tarefa
            </button>

        </form>

        <!-- Lista montada a partir do array $tasks. -->
        <ul>

            <?php foreach ($tasks as $task): ?>

                <?php
                    // Verifica se a tarefa está vencida e ainda não foi concluída.
                    $isExpired =
                        !(bool) $task['done'] &&
                        $task['due_date'] < date('Y-m-d');
                ?>

                <li class="<?php echo $task['done'] ? 'done' : ''; ?> <?php echo $isExpired ? 'expired' : ''; ?>">

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
                            <?php echo htmlspecialchars($task['responsible'] ?? 'Não informado'); ?>
                        </small>

                    </div>

                    <!-- Links de ação enviados para o roteador. -->
                    <div class="actions">

                        <?php if (!$task['done']): ?>
                            <a
                                href="index.php?action=complete&id=<?php echo (int) $task['id']; ?>"
                                title="Concluir"
                            >
                                ✅
                            </a>
                        <?php endif; ?>

                        <a
                            href="index.php?action=delete&id=<?php echo (int) $task['id']; ?>"
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
