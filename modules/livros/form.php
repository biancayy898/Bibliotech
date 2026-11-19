
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$mensagem = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    $titulo = trim($_POST['titulo']);
    $autor = trim($_POST['autor']);
    $ano = filter_input(INPUT_POST, 'ano', FILTER_VALIDATE_INT);
    $quantidade_total = filter_input(INPUT_POST, 'quantidade_total', FILTER_VALIDATE_INT);
    $quantidade_disponivel = filter_input(INPUT_POST, 'quantidade_disponivel', FILTER_VALIDATE_INT);

    if (
        !$id ||
        $titulo === '' ||
        $autor === '' ||
        $ano === false || $ano === null ||
        $quantidade_total === false || $quantidade_total === null ||
        $quantidade_disponivel === false || $quantidade_disponivel === null
    ) {
        $mensagem = 'Preencha todos os campos corretamente.';

    } elseif (
        $quantidade_total < 0 ||
        $quantidade_disponivel < 0 ||
        $quantidade_disponivel > $quantidade_total
    ) {
        $mensagem = 'Erro: quantidade disponível inválida!';

    } else {

        $stmt = $pdo->prepare(
            "UPDATE livros
             SET titulo = :titulo,
                 autor = :autor,
                 ano = :ano,
                 quantidade_total = :total,
                 quantidade_disponivel = :disponivel
             WHERE id = :id"
        );

        $stmt->execute([
            'titulo' => $titulo,
            'autor' => $autor,
            'ano' => $ano,
            'total' => $quantidade_total,
            'disponivel' => $quantidade_disponivel,
            'id' => $id
        ]);

        $mensagem = 'Livro atualizado com sucesso!';
    }
}

if (!$id) {
    die('ID inválido.');
}

$stmt = $pdo->prepare(
    "SELECT id, titulo, autor, ano,
            quantidade_total, quantidade_disponivel
     FROM livros
     WHERE id = :id"
);

$stmt->execute(['id' => $id]);
$livro = $stmt->fetch();

if (!$livro) {
    die('Livro não encontrado.');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar livro</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="card">

        <h1>Editar livro</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <input type="hidden" name="id"
                   value="<?= $livro['id'] ?>">

            <label>Título</label>
            <input type="text" name="titulo"
                   value="<?= htmlspecialchars($livro['titulo']) ?>"
                   required>

            <label>Autor</label>
            <input type="text" name="autor"
                   value="<?= htmlspecialchars($livro['autor']) ?>"
                   required>

            <label>Ano</label>
            <input type="number" name="ano"
                   value="<?= htmlspecialchars($livro['ano']) ?>"
                   required>

            <label>Quantidade total</label>
            <input type="number" name="quantidade_total"
                   min="0"
                   value="<?= $livro['quantidade_total'] ?>"
                   required>

            <label>Quantidade disponível</label>
            <input type="number" name="quantidade_disponivel"
                   min="0"
                   max="<?= $livro['quantidade_total'] ?>"
                   value="<?= $livro['quantidade_disponivel'] ?>"
                   required>

            <button type="submit">Salvar alterações</button>

        </form>

    </div>
</main>

</body>
</html>
