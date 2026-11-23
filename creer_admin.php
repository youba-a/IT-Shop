<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$login = $argv[1] ?? '';
$role = $argv[2] ?? '';
if (!preg_match('/\A[a-zA-Z0-9_.-]{1,50}\z/', $login) || !in_array($role, ['gerant', 'assistant'], true)) {
    fwrite(STDERR, "Usage : php creer_admin.php <login> <gerant|assistant>\n");
    exit(1);
}

fwrite(STDERR, "Saisissez le mot de passe (la saisie peut être visible) : ");
$password = rtrim((string) fgets(STDIN), "\r\n");
if (strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
    fwrite(STDERR, "Le mot de passe doit contenir entre 12 et 72 octets, sans octet nul.\n");
    exit(1);
}

require_once __DIR__ . '/connect.php';
$hash = password_hash($password, PASSWORD_DEFAULT);
try {
    $stmt = $connexionDB->prepare('INSERT INTO admin (login, mot_de_passe_hash, role) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $login, $hash, $role);
    $stmt->execute();
    echo "Compte créé.\n";
} catch (mysqli_sql_exception $exception) {
    fwrite(STDERR, "Impossible de créer le compte. Vérifiez le schéma et la disponibilité du login.\n");
    exit(1);
}
