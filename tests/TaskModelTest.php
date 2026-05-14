<?php
// ==========================================
// TESTES DO MODEL DE TAREFAS
// ==========================================

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Model/Task.php';

class TaskModelTest extends TestCase
{
    // Cria um banco em memória para testar sem alterar o banco real.
    private function makeModel(): Task
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return new Task($pdo);
    }

    // Não deve salvar tarefa sem título.
    public function testNaoPodeSalvarTarefaSemTitulo(): void
    {
        $model = $this->makeModel();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Título e Data são obrigatórios.');

        $model->save('', 'Fazer compras', '2026-12-31', 'Kevin');
    }

    // Não deve salvar tarefa sem data.
    public function testNaoPodeSalvarTarefaSemData(): void
    {
        $model = $this->makeModel();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Título e Data são obrigatórios.');

        $model->save('Fazer compras', 'Comprar pão', '', 'Kevin');
    }

    // Não deve salvar tarefa sem responsável.
    public function testNaoPodeSalvarTarefaSemResponsavel(): void
    {
        $model = $this->makeModel();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Responsável é obrigatório.');

        $model->save('Fazer compras', 'Comprar pão', '2026-12-31', '');
    }

    // Não deve salvar tarefa com data anterior ao dia atual.
    public function testNaoPodeSalvarTarefaComDataVencida(): void
    {
        $model = $this->makeModel();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Não é permitido criar tarefas com data vencida!');

        $model->save('Fazer compras', 'Comprar pão', '2000-01-01', 'Kevin');
    }
}
