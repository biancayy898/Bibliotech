
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: index.php');
    exit;
}

$mensagem = '';
$tipoMensagem = '';

try {

    // Iniciar transação
    $pdo->beginTransaction();

    // 1. Buscar o empréstimo
    $sql = "SELECT id, livro_id, status
            FROM emprestimos
            WHERE id = :id
            FOR UPDATE";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id
    ]);

    $emprestimo = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Verificar se o empréstimo existe
    if (!$emprestimo) {
        throw new Exception('Empréstimo não encontrado.');
    }

    // 3. Verificar se ainda está ativo
    if ($emprestimo['status'] !== 'ativo') {
        throw new Exception('Este empréstimo já foi devolvido anteriormente.');
    }

    // 4. Registrar a data real da devolução
    // e alterar o status
    $sql = "UPDATE emprestimos
            SET status = 'devolvido',
                data_devolucao_real = CURDATE()
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id
    ]);

    // 5. Aumentar a quantidade disponível do livro
    $sql = "UPDATE livros
            SET quantidade_disponivel = quantidade_disponivel + 1
            WHERE id = :livro_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':livro_id' => $emprestimo['livro_id']
    ]);

    // 6. Confirmar as alterações
    $pdo->commit();

    $mensagem = 'Devolução registrada com sucesso!';
    $tipoMensagem = 'sucesso';

} catch (Exception $e) {

    // Desfazer alterações em caso de erro
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $mensagem = $e->getMessage();
    $tipoMensagem = 'erro';
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Devolução de Livro</title>

    <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">

    <div class="card">

        <h1>Registrar devolução</h1>

        <div class="alert">

            <?php if ($tipoMensagem === 'sucesso'): ?>

                <p style="color: green;">
                    <?= htmlspecialchars($mensagem) ?>
                </p>

            <?php else: ?>

                <p style="color: red;">
                    <?= htmlspecialchars($mensagem) ?>
                </p>

            <?php endif; ?>

        </div>

        <a class="button" href="index.php">
            Voltar
        </a>

    </div>

</main>

</body>
</html>
