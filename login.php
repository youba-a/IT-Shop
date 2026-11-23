<?php

require_once __DIR__ . '/session.php';
requireCsrf();
require_once __DIR__ . '/connect.php';

$username = trim(postText('username'));
$password = postText('password');
$stmt = $connexionDB->prepare('SELECT id, login, mot_de_passe_hash, role FROM admin WHERE login = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();

if ($account && in_array($account['role'], ['gerant', 'assistant'], true)) {
    $hash = $account['mot_de_passe_hash'];
    $legacy = preg_match('/\A[a-f0-9]{64}\z/', $hash) === 1;
    $valid = $legacy ? hash_equals($hash, hash('sha256', trim($password))) : password_verify($password, $hash);

    if ($valid) {
        if ($legacy || password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $newHash = password_hash($legacy ? trim($password) : $password, PASSWORD_DEFAULT);
            $update = $connexionDB->prepare('UPDATE admin SET mot_de_passe_hash = ? WHERE id = ? AND mot_de_passe_hash = ?');
            $update->bind_param('sis', $newHash, $account['id'], $hash);
            $update->execute();
        }
        session_regenerate_id(true);
        $_SESSION = ['nom' => htmlspecialchars($account['login']), 'role' => $account['role']];
        header('Location: admin.php');
        exit;
    }
}

$_SESSION['error'] = 'Identifiants incorrects.';
header('Location: auth.php');
exit;
