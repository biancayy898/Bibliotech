
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$mensagem = '';

$usuarios = $pdo->query(
    "SELECT id, nome FROM usuarios
     WHERE tipo = 'aluno' AND status = 'ativo'
     ORDER BY nome"
)->fetchAll();

$livros = $pdo->query(
    "SELECT id, titulo, quantidade_disponivel
     FROM livros
     WHERE quantidade_disponivel > 0
     ORDER BY titulo"
)->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $livro_id = filter_input(INPUT_POST, 'livro_id', FILTER_VALIDATE_INT);
    $data_devolucao_prevista = $_POST['data_devolucao_prevista'] ?? '';

    try {

        if (!$usuario_id || !$livro_id || empty($data_devolucao_prevista)) {
            throw new Exception('Preencha todos os campos corretamente.');
        }

        $pdo->beginTransaction();

        // Verificar se o aluno está ativo
        $stmt = $pdo->prepare(
            "SELECT id FROM usuarios
             WHERE id = ? AND tipo = 'aluno' AND status = 'ativo'"
        );
        $stmt->execute([$usuario_id]);

        if (!$stmt->fetch()) {
            throw new Exception('Aluno inválido ou inativo.');
        }

        // Verificar disponibilidade do livro
        $stmt = $pdo->prepare(
            "SELECT id, quantidade_disponivel
             FROM livros
             WHERE id = ?
             FOR UPDATE"
        );
        $stmt->execute([$livro_id]);

        $livro = $stmt->fetch();

        if (!$livro || $livro['quantidade_disponivel'] <= 0) {
            throw new Exception('Livro indisponível para empréstimo.');
        }

        // Registrar empréstimo
        $stmt = $pdo->prepare(
            "INSERT INTO emprestimos
             (usuario_id, livro_id, data_retirada,
              data_devolucao_prevista, status)
             VALUES (?, ?, CURDATE(), ?, 'ativo')"
        );

        $stmt->execute([
            $usuario_id,
            $livro_id,
            $data_devolucao_prevista
        ]);

        // Diminuir quantidade disponível em um livro
        $stmt = $pdo->prepare(
            "UPDATE livros
             SET quantidade_disponivel = quantidade_disponivel - 1
             WHERE id = ? AND quantidade_disponivel > 0"
        );

        $stmt->execute([$livro_id]);

        if ($stmt->rowCount() !== 1) {
            throw new Exception('Não foi possível atualizar a quantidade do livro.');
        }

        $pdo->commit();

        $mensagem = 'Empréstimo registrado com sucesso! Quantidade disponível atualizada.';

        // Atualizar lista de livros
        $livros = $pdo->query(
            "SELECT id, titulo, quantidade_disponivel
             FROM livros
             WHERE quantidade_disponivel > 0
             ORDER BY titulo"
        )->fetchAll();

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $mensagem = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo empréstimo</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">
    <div class="card">

        <h1>Novo empréstimo</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <label>Aluno</label>
            <select name="usuario_id" required>
                <option value="">Selecione</option>

                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?= $usuario['id'] ?>">
                        <?= htmlspecialchars($usuario['nome']) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <label>Livro</label>
            <select name="livro_id" required>
                <option value="">Selecione</option>

                <?php foreach ($livros as $livro): ?>
                    <option value="<?= $livro['id'] ?>">
                        <?= htmlspecialchars($livro['titulo']) ?>
                        — <?= $livro['quantidade_disponivel'] ?> disponível(is)
                    </option>
                <?php endforeach; ?>

            </select>

            <label>Data prevista para devolução</label>
            <input type="date"
                   name="data_devolucao_prevista"
                   min="<?= date('Y-m-d') ?>"
                   required>

            <button type="submit">Registrar empréstimo</button>

        </form>

    </div>
</main>

</body>
</html>
