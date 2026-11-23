<?php

function uploadProductImage(?array $file): string
{
    $extensions = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null)) {
        throw new InvalidArgumentException("Le téléchargement de l'image a échoué.");
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new InvalidArgumentException("L'image ne doit pas dépasser 5 Mo.");
    }
    $info = @getimagesize($file['tmp_name']);
    $extension = $extensions[$info[2] ?? 0] ?? null;
    if ($extension === null) {
        throw new InvalidArgumentException('Formats acceptés : JPEG, PNG, GIF et WebP.');
    }
    $name = 'prod_' . bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/img/' . $name)) {
        throw new InvalidArgumentException("Impossible d'enregistrer l'image.");
    }
    return $name;
}

function removeUploadedProductImage(?string $name): void
{
    if ($name && preg_match('/\Aprod_[a-f0-9]+(?:\.[0-9]+)?\.(?:jpe?g|png|gif|webp)\z/i', $name)) {
        $path = __DIR__ . '/img/' . $name;
        if (is_file($path) && !@unlink($path) && is_file($path)) {
            error_log("Impossible de supprimer une image de produit.");
        }
    }
}
