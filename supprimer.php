<?php

require_once __DIR__ . '/session.php';
if (($_SESSION['role'] ?? null) !== 'gerant') {
    header('Location: auth.php');
    exit;
}
requireCsrf();
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/image_produit.php';

$id = filter_var(postText('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$message = 'Produit introuvable.';
if ($id !== false) {
    $product = null;
    $connexionDB->begin_transaction();
    try {
        $stmt = $connexionDB->prepare('SELECT photo_produit FROM produits WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        if ($product) {
            $stmt = $connexionDB->prepare('DELETE FROM produits WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $message = 'Produit supprimé avec succès !';
        }
        $connexionDB->commit();
    } catch (mysqli_sql_exception $exception) {
        $connexionDB->rollback();
        error_log($exception->getMessage());
        $product = null;
        $message = 'Impossible de supprimer le produit.';
    }
    if ($product) {
        removeUploadedProductImage($product['photo_produit']);
    }
}

$_SESSION['feedback_message'] = '<div class="alert alert-info">' . $message . '</div>';
header('Location: admin.php');
exit;
