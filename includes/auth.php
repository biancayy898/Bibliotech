
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// VERIFICAR LOGIN
function exigirLogin(): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login.php');
        exit;
    }
}

// VERIFICAR PERFIL
function exigirPerfil(array $perfis): void
{
    exigirLogin();

    if (
        !isset($_SESSION['tipo']) ||
        !in_array($_SESSION['tipo'], $perfis, true)
    ) {
        header('Location: /acesso_negado.php');
        exit;
    }
}

// SOMENTE ADMINISTRADOR
function exigirAdmin(): void
{
    exigirPerfil(['admin']);
}

// ADMINISTRADOR E PROFESSOR
function exigirAdminOuProfessor(): void
{
    exigirPerfil(['admin', 'professor']);
}

// SOMENTE ALUNO
function exigirAluno(): void
{
    exigirPerfil(['aluno']);
}
