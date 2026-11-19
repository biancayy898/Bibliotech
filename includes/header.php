
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tipo = $_SESSION['tipo'] ?? '';

?>

<header class="topbar">

    <a class="brand" href="../dashboard/index.php">
        📚 BiblioTech
    </a>

    <nav>

        <a href="../dashboard/index.php">Dashboard</a>

        <?php if ($tipo === 'admin'): ?>

            <!-- ADMINISTRADOR -->
            <a href="../usuarios/index.php">Usuários</a>
            <a href="../livros/index.php">Livros</a>
            <a href="../emprestimos/index.php">Empréstimos</a>

        <?php elseif ($tipo === 'professor'): ?>

            <!-- PROFESSOR -->
            <a href="../usuarios/index.php">Alunos</a>
            <a href="../livros/index.php">Livros</a>
            <a href="../emprestimos/index.php">Empréstimos</a>

        <?php elseif ($tipo === 'aluno'): ?>

            <!-- ALUNO -->
            <a href="../livros/index.php">Livros</a>
            <a href="../emprestimos/meus.php">Meus empréstimos</a>

        <?php endif; ?>

        <a href="../../logout.php">Sair</a>

    </nav>

</header>
