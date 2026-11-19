
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirLogin();

$livros = $pdo->query(
    'SELECT id, titulo, autor, ano_publicacao,
            quantidade_total, quantidade_disponivel
     FROM livros ORDER BY titulo'
)->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livros — BiblioTech</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">

    <div class="page-title">
        <h1>Acervo</h1>
    </div>

    <div class="card">

        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Ano</th>
                    <th>Total</th>
                    <th>Disponíveis</th>
                    <?php if (in_array($_SESSION['tipo'], ['admin', 'professor'], true)): ?>
                        <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($livros as $livro): ?>
                <tr>
                    <td><?= htmlspecialchars($livro['titulo']) ?></td>
                    <td><?= htmlspecialchars($livro['autor']) ?></td>
                    <td><?= htmlspecialchars($livro['ano_publicacao'] ?? '') ?></td>
                    <td><?= $livro['quantidade_total'] ?></td>
                    <td><?= $livro['quantidade_disponivel'] ?></td>

                    <?php if (in_array($_SESSION['tipo'], ['admin', 'professor'], true)): ?>
                        <td>
                            <a href="form.php?id=<?= $livro['id'] ?>">Editar</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>

        </table>

    </div>

</main>

</body>
</html>
