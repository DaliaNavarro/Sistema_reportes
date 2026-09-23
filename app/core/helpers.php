<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $route): never
{
    header('Location: index.php?route=' . urlencode($route));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' .
        e(csrf_token()) .
        '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';

    if (
        empty($_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $token)
    ) {
        http_response_code(419);
        exit('Token CSRF inválido.');
    }
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['flash'][$key] ?? null;

    unset($_SESSION['flash'][$key]);

    return $message;
}

function view(string $file, array $data = []): void
{
    extract($data);

    require dirname(__DIR__) . '/views/' . $file . '.php';
}

function generate_folio(PDO $db, ?string $fechaReferencia = null): string
{
    $year = $fechaReferencia
        ? (int)date('Y', strtotime($fechaReferencia))
        : (int)date('Y');

    $stmt = $db->query("
        SELECT id
        FROM reportes
        ORDER BY id DESC
        LIMIT 1
    ");

    $last = $stmt->fetch();

    $number = $last ? ((int)$last['id'] + 1) : 1;

    return sprintf('TI-%s-%06d', $year, $number);
}