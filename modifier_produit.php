<?php
require_once __DIR__ . '/session.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['gerant', 'assistant'], true)) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/image_produit.php';

$role = $_SESSION['role'];

//  Vérification de l'ID produit
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de produit invalide.");
}

$id = (int)$_GET['id'];

//  Récupération des infos produit
$sql = "SELECT * FROM produits WHERE id = ?";
$stmt = $connexionDB->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produit = $result->fetch_assoc();
$stmt->close();

if (!$produit) {
    die("Produit introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $description = trim(postText('description'));
    $errors = [];
    if (mb_strlen($description) < 3 || mb_strlen($description) > 1000) {
        $errors[] = 'La description doit contenir entre 3 et 1000 caractères.';
    }

    if ($role === 'gerant') {
        $nom = trim(postText('nom'));
        $categorie = trim(postText('categorie'));
        $marque = trim(postText('marque'));
        $prix = filter_var(postText('prix'), FILTER_VALIDATE_FLOAT);
        $quantite = filter_var(postText('quantite_en_stock'), FILTER_VALIDATE_INT);
        $evaluation = filter_var(postText('evaluation_moyenne'), FILTER_VALIDATE_FLOAT);
        $statut = postText('statut');

        if (mb_strlen($nom) < 3 || mb_strlen($nom) > 50
            || mb_strlen($categorie) < 3 || mb_strlen($categorie) > 50
            || mb_strlen($marque) < 2 || mb_strlen($marque) > 50) {
            $errors[] = 'Vérifiez la longueur du nom, de la catégorie et de la marque.';
        }
        if ($prix === false || $prix < 0.1 || $prix > 1000000
            || $quantite === false || $quantite < 0 || $quantite > 1000000
            || $evaluation === false || $evaluation < 0 || $evaluation > 5
            || !in_array($statut, ['disponible', 'en rupture'], true)) {
            $errors[] = 'Vérifiez le prix, la quantité, la note et le statut.';
        }
    }

    if (!$errors) {
        $newPhoto = null;
        $previousPhoto = null;
        try {
            if ($role === 'gerant') {
                if (($_FILES['photo_produit']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $newPhoto = uploadProductImage($_FILES['photo_produit']);
                }
                $connexionDB->begin_transaction();
                $stmt = $connexionDB->prepare('SELECT photo_produit FROM produits WHERE id = ? FOR UPDATE');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $current = $stmt->get_result()->fetch_assoc();
                if (!$current) {
                    throw new InvalidArgumentException('Produit introuvable.');
                }
                $previousPhoto = $current['photo_produit'];
                $photo = $newPhoto ?? $previousPhoto;
                $stmt = $connexionDB->prepare(
                    'UPDATE produits SET nom = ?, description = ?, prix = ?, quantite_en_stock = ?,
                     categorie = ?, marque = ?, evaluation_moyenne = ?, statut = ?, photo_produit = ? WHERE id = ?'
                );
                $stmt->bind_param('ssdissdssi', $nom, $description, $prix, $quantite, $categorie, $marque, $evaluation, $statut, $photo, $id);
                $stmt->execute();
                $connexionDB->commit();
            } else {
                $stmt = $connexionDB->prepare('UPDATE produits SET description = ? WHERE id = ?');
                $stmt->bind_param('si', $description, $id);
                $stmt->execute();
            }

            if ($newPhoto !== null) {
                removeUploadedProductImage($previousPhoto);
            }
            $_SESSION['success_message'] = 'Produit modifié avec succès !';
            header('Location: detail_produit.php?id=' . $id);
            exit;
        } catch (InvalidArgumentException | mysqli_sql_exception $exception) {
            if ($role === 'gerant') {
                $connexionDB->rollback();
            }
            removeUploadedProductImage($newPhoto);
            if ($exception instanceof mysqli_sql_exception) {
                error_log($exception->getMessage());
                $errors[] = 'Impossible de modifier le produit.';
            } else {
                $errors[] = $exception->getMessage();
            }
        }
    }
    $_SESSION['error_message'] = implode('<br>', array_map('htmlspecialchars', $errors));
    header('Location: modifier_produit.php?id=' . $id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier produit</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body>
<header>
    <div class="container header-content">
        <div class="logo">
            <img src="img/logo/Logo.png" alt="it_SHOP Logo">
        </div>
        <div class="text-center flex-grow-1">
            <h5 class="mb-0">
                Bonjour <span class="text-primary">
                    <?php echo htmlspecialchars($role === 'gerant' ? 'admin' : 'assistant'); ?>
                </span>
            </h5>
        </div>
        <nav class="main-navigation">
            <ul>
                <li><a href="index.php">ACCUEIL</a></li>
                <li><a href="admin.php">Admin</a></li>
                <li><form method="post" action="logout.php"><?= csrfField() ?><button type="submit" class="btn btn-link">Déconnecter</button></form></li>
            </ul>
        </nav>
    </div>
</header>
<div class="container my-5">
    <h2 class="mb-4 text-center">Modifier le produit : <?= htmlspecialchars($produit['nom']) ?></h2>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>


    <form method="POST" enctype="multipart/form-data" class="border p-4 rounded bg-light shadow-sm">
        <?= csrfField() ?>
        <div class="row g-3">

            <?php if ($role === 'gerant'): ?>
                <!-- Champs gérant -->
                <div class="col-md-6">
                    <label>Nom :</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>" class="form-control" required minlength="3" maxlength="50">
                </div>
                <div class="col-md-6">
                    <label>Prix (€) :</label>
                    <input type="number" name="prix" step="0.01" value="<?= htmlspecialchars($produit['prix']) ?>" class="form-control" required min="0.1" max="1000000"
                     onkeydown="if(event.key==='e'||event.key==='E'||event.key==='+'||event.key==='-')event.preventDefault();">
                </div>
                <div class="col-md-6">
                    <label>Quantité en stock :</label>
                    <input type="number" name="quantite_en_stock" value="<?= htmlspecialchars($produit['quantite_en_stock']) ?>" class="form-control" required min="0" max="1000000"
                     onkeydown="if(event.key==='e'||event.key==='E'||event.key==='+'||event.key==='-')event.preventDefault();">
                </div>
                <div class="col-md-6">
                    <label>Catégorie :</label>
                    <input type="text" name="categorie" value="<?= htmlspecialchars($produit['categorie']) ?>" class="form-control" required minlength="3" maxlength="50">
                </div>
                <div class="col-md-6">
                    <label>Marque :</label>
                    <input type="text" name="marque" value="<?= htmlspecialchars($produit['marque']) ?>" class="form-control" required minlength="2" maxlength="50"
                     onkeydown="if(event.key === '+' || event.key === '-') event.preventDefault();">
                </div>
                <div class="col-md-6">
                    <label>Date d’ajout :</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($produit['date_ajout']) ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label>Évaluation moyenne :</label>
                    <input type="number" name="evaluation_moyenne" step="0.1" min="0" max="5" value="<?= htmlspecialchars($produit['evaluation_moyenne']) ?>" class="form-control" required
                    pattern="^([0-4](\.\d)?|5(\.0)?)$"
                    title="Entrez un nombre entre 0 et 5, avec au maximum un chiffre après la virgule."
                    onkeydown="if(event.key === 'e' || event.key === 'E'|| event.key === '+' || event.key === '-') event.preventDefault();">
                </div>
                <div class="col-md-6">
                    <label>Statut :</label>
                    <select name="statut" class="form-control" required>
                        <option value="disponible" <?= $produit['statut'] === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="en rupture" <?= $produit['statut'] === 'en rupture' ? 'selected' : '' ?>>En rupture</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Photo actuelle :</label><br>
                    <?php if (!empty($produit['photo_produit'])): ?>
                        <img src="img/<?= htmlspecialchars($produit['photo_produit']) ?>" alt="photo" width="150" class="border rounded">
                    <?php else: ?>
                        <em>Aucune image</em>
                    <?php endif; ?>

                </div>
                <div class="col-md-6">
                    <label>Changer la photo :</label>
                    <input type="file" name="photo_produit" accept="image/*" class="form-control" >
                    <small class="form-text text-muted">Formats acceptés : JPG, JPEG, PNG, GIF, WEBP. Max 5MB.</small>
                </div>
                            <script>
                function checkFileSize(input) {
                    const file = input.files[0];
                    const errorDiv = document.getElementById('photo_error');
                    errorDiv.textContent = ''; // réinitialise le message

                    if (file && file.size > 5 * 1024 * 1024) { // 5MB
                        errorDiv.textContent = "L'image est trop volumineuse (max 5MB).";
                        input.value = ""; // vide le champ pour forcer l'utilisateur à choisir à nouveau
                    }
                }
                </script>
            <?php endif; ?>

            <!-- Champ description commun -->
            <div class="col-12">
                <label>Description :</label>
                <textarea name="description" rows="4" class="form-control" required minlength="3" maxlength="1000"><?= htmlspecialchars($produit['description']) ?></textarea>
            </div>

        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="detail_produit.php?id=<?= $id ?>" class="btn btn-secondary">← Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </div>
    </form>
</div>
</body>
    <footer>
        <div class="container py-3">
            <div class="row">
            <div class="col-md-2 mb-4">
                <h5 class="text-uppercase mb-4">Navigation</h5>
                <ul class="list-unstyled">
                <li><a href="index.php" class="text-decoration-none">Accueil</a></li>
                <li><a href="produit.php" class="text-decoration-none">Nos Produits</a></li>
                <li><a href="contact.php" class="text-decoration-none">Contact</a></li>
                <li><a href="auth.php" class="text-decoration-none">Se connecter</a></li>
                </ul>
            </div>

            <div class="col-md-3 mb-4">
                <h5 class="text-uppercase mb-4">Contactez-nous</h5>
                <ul class="list-unstyled">
                <li><i class="fas fa-envelope me-2"></i> <a href="contact.php" class="text-decoration-none">Formulaire de contact</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h5 class="text-uppercase mb-4">Suivez-nous</h5>
                <div class="social-links d-flex">
                    <a href="" target="_blank" class="text-muted me-3"><i class="fab fa-facebook-f fa-2x"></i></a>
                    <a href="" target="_blank" class="text-muted me-3"><i class="fab fa-twitter fa-2x"></i></a>
                    <a href="" target="_blank" class="text-muted"><i class="fab fa-instagram fa-2x"></i></a>
                </div>
            </div>
            </div> <!-- fermeture .row -->

            <hr class="my-3 border-secondary">

            <div class="text-center text-muted py-2">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> IT_SHOP. Tous droits réservés.</p>
            </div>
        </div>
    </footer>
    </html>
