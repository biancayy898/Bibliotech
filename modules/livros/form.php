<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$livro = [
    'titulo' => '',
    'autor' => '',
    'ano_publicacao' => '',
    'quantidade_total' => 1,
    'quantidade_disponivel' => 1
];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM livros WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $livro = $stmt->fetch() ?: $livro;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $autor = trim($_POST['autor'] ?? '');
    $ano_publicacao = filter_input(INPUT_POST, 'ano_publicacao', FILTER_VALIDATE_INT);
    $quantidade_total = filter_input(INPUT_POST, 'quantidade_total', FILTER_VALIDATE_INT);

    if (
        $titulo === '' ||
        $autor === '' ||
        !$ano_publicacao ||
        $ano_publicacao < 1 ||
        !$quantidade_total ||
        $quantidade_total < 1
    ) {
        $mensagem = 'Preencha todos os campos corretamente.';
    } else {

        $quantidade_disponivel = $quantidade_total;

        $stmt = $pdo->prepare(
            'INSERT INTO livros
            (titulo, autor, ano_publicacao, quantidade_total, quantidade_disponivel)
            VALUES
            (:titulo, :autor, :ano_publicacao, :quantidade_total, :quantidade_disponivel)'
        );

        $stmt->execute([
            'titulo' => $titulo,
            'autor' => $autor,
            'ano_publicacao' => $ano_publicacao,
            'quantidade_total' => $quantidade_total,
            'quantidade_disponivel' => $quantidade_disponivel
        ]);

        header('Location: index.php');
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id ? 'Editar' : 'Novo' ?> livro</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="card">
        <h1><?= $id ? 'Editar livro' : 'Novo livro' ?></h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Título</label>
            <input type="text" name="titulo" value="<?= htmlspecialchars($livro['titulo']) ?>" required>

            <label>Autor</label>
            <input type="text" name="autor" value="<?= htmlspecialchars($livro['autor']) ?>" required>

            <label>Ano de publicação</label>
            <input type="number" name="ano_publicacao" value="<?= htmlspecialchars($livro['ano_publicacao']) ?>">

            <label>Quantidade total</label>
            <input type="number" name="quantidade_total" min="1" value="<?= $livro['quantidade_total'] ?>" required>

            <button type="submit">Salvar</button>
        </form>
    </div>
</main>
</body>
</html>
