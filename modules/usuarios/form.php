
<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../database/conexao.php';

exigirPerfil(['admin', 'professor']);

// RECEBER ID PELA URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$modoEdicao = isset($_GET['id']);

$usuarioExiste = false;

$mensagem = '';

$usuario = [
    'nome' => '',
    'email' => '',
    'tipo' => 'aluno',
    'status' => 'ativo'
];

try {

    // LOCALIZAR USUÁRIO PARA EDIÇÃO
    if ($modoEdicao) {

        if (!$id || $id <= 0) {

            $mensagem = 'ID de usuário inválido.';

        } else {

            $stmt = $pdo->prepare(
                'SELECT * FROM usuarios WHERE id = :id'
            );

            $stmt->execute([
                'id' => $id
            ]);

            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {

                $usuario = $resultado;
                $usuarioExiste = true;

            } else {

                $mensagem = 'Usuário não encontrado.';
            }
        }

    } else {

        // MODO CADASTRO
        $usuarioExiste = true;
    }

    // RECEBER DADOS DO FORMULÁRIO
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $nome = is_string($_POST['nome'] ?? null)
            ? trim($_POST['nome'])
            : '';

        $email = is_string($_POST['email'] ?? null)
            ? trim($_POST['email'])
            : '';

        $tipo = is_string($_POST['tipo'] ?? null)
            ? $_POST['tipo']
            : '';

        $status = is_string($_POST['status'] ?? null)
            ? $_POST['status']
            : '';

        $senha = is_string($_POST['senha'] ?? null)
            ? $_POST['senha']
            : '';

        // MANTER DADOS NO FORMULÁRIO
        $usuario['nome'] = $nome;
        $usuario['email'] = $email;
        $usuario['tipo'] = $tipo;
        $usuario['status'] = $status;

        if (!$usuarioExiste) {

            $mensagem = 'Usuário não encontrado.';

        } elseif (
            $nome === '' ||
            $email === '' ||
            (!$modoEdicao && $senha === '')
        ) {

            $mensagem = 'Preencha todos os campos obrigatórios.';

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $mensagem = 'Informe um e-mail válido.';

        } elseif (
            !in_array($tipo, ['aluno', 'professor', 'admin'], true) ||
            !in_array($status, ['ativo', 'bloqueado'], true)
        ) {

            $mensagem = 'Tipo ou status inválido.';

        } else {

            // VERIFICAR E-MAIL DUPLICADO
            if ($modoEdicao) {

                $stmt = $pdo->prepare(
                    'SELECT id FROM usuarios
                     WHERE email = :email AND id != :id'
                );

                $stmt->execute([
                    'email' => $email,
                    'id' => $id
                ]);

            } else {

                $stmt = $pdo->prepare(
                    'SELECT id FROM usuarios
                     WHERE email = :email'
                );

                $stmt->execute([
                    'email' => $email
                ]);
            }

            if ($stmt->fetch()) {

                $mensagem = 'Este e-mail já está cadastrado.';

            } else {

                // EDITAR USUÁRIO
                if ($modoEdicao) {

                    if ($senha !== '') {

                        // CRIPTOGRAFAR NOVA SENHA
                        $senhaHash = password_hash(
                            $senha,
                            PASSWORD_DEFAULT
                        );

                        // ATUALIZAR TODOS OS DADOS
                        $stmt = $pdo->prepare(
                            'UPDATE usuarios SET
                                nome = :nome,
                                email = :email,
                                senha = :senha,
                                tipo = :tipo,
                                status = :status
                             WHERE id = :id'
                        );

                        $stmt->execute([
                            'nome' => $nome,
                            'email' => $email,
                            'senha' => $senhaHash,
                            'tipo' => $tipo,
                            'status' => $status,
                            'id' => $id
                        ]);

                    } else {

                        // ATUALIZAR SEM ALTERAR A SENHA
                        $stmt = $pdo->prepare(
                            'UPDATE usuarios SET
                                nome = :nome,
                                email = :email,
                                tipo = :tipo,
                                status = :status
                             WHERE id = :id'
                        );

                        $stmt->execute([
                            'nome' => $nome,
                            'email' => $email,
                            'tipo' => $tipo,
                            'status' => $status,
                            'id' => $id
                        ]);
                    }

                    $mensagem = 'Usuário atualizado com sucesso!';

                    // BUSCAR DADOS ATUALIZADOS
                    $stmt = $pdo->prepare(
                        'SELECT * FROM usuarios WHERE id = :id'
                    );

                    $stmt->execute([
                        'id' => $id
                    ]);

                    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                } else {

                    // CADASTRAR NOVO USUÁRIO
                    $senhaHash = password_hash(
                        $senha,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $pdo->prepare(
                        'INSERT INTO usuarios
                        (nome, email, senha, tipo, status)
                        VALUES
                        (:nome, :email, :senha, :tipo, :status)'
                    );

                    $stmt->execute([
                        'nome' => $nome,
                        'email' => $email,
                        'senha' => $senhaHash,
                        'tipo' => $tipo,
                        'status' => $status
                    ]);

                    $mensagem = 'Usuário cadastrado com sucesso!';

                    // LIMPAR FORMULÁRIO
                    $usuario = [
                        'nome' => '',
                        'email' => '',
                        'tipo' => 'aluno',
                        'status' => 'ativo'
                    ];
                }
            }
        }
    }

} catch (PDOException $e) {

    $mensagem = 'Não foi possível realizar a operação. Verifique os dados e tente novamente.';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?= $modoEdicao ? 'Editar' : 'Novo' ?> usuário
    </title>

    <link rel="stylesheet"
          href="../../assets/css/style.css">
</head>

<body>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container">

    <div class="card">

        <h1>
            <?= $modoEdicao ? 'Editar usuário' : 'Novo usuário' ?>
        </h1>

        <?php if ($mensagem !== ''): ?>

            <div class="alert">
                <?= htmlspecialchars(
                    $mensagem,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </div>

        <?php endif; ?>

        <?php if ($usuarioExiste): ?>

            <form method="post">

                <label>Nome</label>

                <input
                    type="text"
                    name="nome"
                    value="<?= htmlspecialchars(
                        $usuario['nome'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >

                <label>E-mail</label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars(
                        $usuario['email'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >

                <label>
                    Senha
                    <?= $modoEdicao
                        ? '(deixe vazia para manter a senha atual)'
                        : ''
                    ?>
                </label>

                <input
                    type="password"
                    name="senha"
                    <?= $modoEdicao ? '' : 'required' ?>
                >

                <label>Tipo</label>

                <select name="tipo" required>

                    <option value="aluno"
                        <?= ($usuario['tipo'] ?? '') === 'aluno'
                            ? 'selected'
                            : '' ?>>
                        Aluno
                    </option>

                    <option value="professor"
                        <?= ($usuario['tipo'] ?? '') === 'professor'
                            ? 'selected'
                            : '' ?>>
                        Professor
                    </option>

                    <option value="admin"
                        <?= ($usuario['tipo'] ?? '') === 'admin'
                            ? 'selected'
                            : '' ?>>
                        Administrador
                    </option>

                </select>

                <label>Status</label>

                <select name="status" required>

                    <option value="ativo"
                        <?= ($usuario['status'] ?? '') === 'ativo'
                            ? 'selected'
                            : '' ?>>
                        Ativo
                    </option>

                    <option value="bloqueado"
                        <?= ($usuario['status'] ?? '') === 'bloqueado'
                            ? 'selected'
                            : '' ?>>
                        Bloqueado
                    </option>

                </select>

                <button type="submit">
                    Salvar
                </button>

            </form>

        <?php endif; ?>

    </div>

</main>

</body>
</html>
