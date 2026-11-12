
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirLogin();

// QUANTIDADE DE LIVROS
$livros = $pdo->query('SELECT COUNT(*) FROM livros')->fetchColumn();

// PERMISSÕES DE ADMINISTRADOR E PROFESSOR
if (in_array($_SESSION['tipo'], ['admin', 'professor'], true)) {

    if ($_SESSION['tipo'] === 'admin') {

        // ADMINISTRADOR VISUALIZA TODOS OS USUÁRIOS
        $usuarios = $pdo->query(
            'SELECT COUNT(*) FROM usuarios'
        )->fetchColumn();

    } else {

        // PROFESSOR VISUALIZA SOMENTE ALUNOS
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE tipo = 'aluno'"
        );

        $stmt->execute();
        $usuarios = $stmt->fetchColumn();
    }

    // EMPRÉSTIMOS ATIVOS
    $emprestimos = $pdo->query(
        "SELECT COUNT(*) FROM emprestimos WHERE status = 'ativo'"
    )->fetchColumn();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard — BiblioTech</title>

    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<main class="container">

    <h1>
        Olá, <?= htmlspecialchars($_SESSION['nome']) ?>!
    </h1>

    <p>
        Perfil:
        <strong>
            <?= htmlspecialchars($_SESSION['tipo']) ?>
        </strong>
    </p>

    <section class="grid">

        <!-- LIVROS: TODOS OS PERFIS -->
        <div class="card">
            <h2>📚 Livros</h2>
            <strong><?= $livros ?></strong>
        </div>

        <?php if ($_SESSION['tipo'] === 'admin'): ?>

            <!-- ADMINISTRADOR -->
            <div class="card">
                <h2>👥 Usuários</h2>
                <strong><?= $usuarios ?></strong>
            </div>

            <div class="card">
                <h2>📋 Empréstimos ativos</h2>
                <strong><?= $emprestimos ?></strong>
            </div>

        <?php elseif ($_SESSION['tipo'] === 'professor'): ?>

            <!-- PROFESSOR -->
            <div class="card">
                <h2>👥 Alunos</h2>
                <strong><?= $usuarios ?></strong>
            </div>

            <div class="card">
                <h2>📋 Empréstimos ativos</h2>
                <strong><?= $emprestimos ?></strong>
            </div>

        <?php elseif ($_SESSION['tipo'] === 'aluno'): ?>

            <!-- ALUNO NÃO VISUALIZA DADOS ADMINISTRATIVOS -->

        <?php endif; ?>

    </section>

    <!-- TAREFAS DE CADA PERFIL -->
    <div class="card">

        <h2>Próximas tarefas</h2>

        <?php if ($_SESSION['tipo'] === 'admin'): ?>

            <ul>
                <li>Visualizar todos os usuários.</li>
                <li>Cadastrar alunos, professores e administradores.</li>
                <li>Editar qualquer usuário.</li>
                <li>Gerenciar livros.</li>
                <li>Registrar empréstimos.</li>
                <li>Registrar devoluções.</li>
            </ul>

        <?php elseif ($_SESSION['tipo'] === 'professor'): ?>

            <ul>
                <li>Visualizar somente alunos.</li>
                <li>Cadastrar alunos.</li>
                <li>Editar alunos.</li>
                <li>Gerenciar livros.</li>
                <li>Registrar empréstimos.</li>
                <li>Registrar devoluções.</li>
            </ul>

        <?php elseif ($_SESSION['tipo'] === 'aluno'): ?>

            <ul>
                <li>Consultar livros.</li>
                <li>Consultar meus próprios empréstimos.</li>
            </ul>

        <?php endif; ?>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

</body>
</html>
