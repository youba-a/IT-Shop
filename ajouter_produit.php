<?php
// Active la mise en mémoire tampon pour gérer les redirections
ob_start();
require_once __DIR__ . '/session.php';

// Vérification stricte du rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'gerant') {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/image_produit.php';

$feedback = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $description = trim(postText('description'));
    $errors = [];
    if (mb_strlen($description) < 3 || mb_strlen($description) > 1000) {
        $errors[] = 'La description doit contenir entre 3 et 1000 caractères.';
    }
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

    if (!$errors) {
        $photo_produit = null;
        try {
            $photo_produit = uploadProductImage($_FILES['photo_produit'] ?? null);
            $date_ajout = date('Y-m-d');
            $stmt = $connexionDB->prepare(
                'INSERT INTO produits
                 (nom, description, prix, quantite_en_stock, categorie, marque, date_ajout, evaluation_moyenne, statut, photo_produit)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('ssdisssdss', $nom, $description, $prix, $quantite, $categorie, $marque, $date_ajout, $evaluation, $statut, $photo_produit);
            $stmt->execute();
            $_SESSION['feedback_message'] = '<div class="alert alert-success">Produit ajouté avec succès !</div>';
            header('Location: admin.php');
            exit;
        } catch (InvalidArgumentException $exception) {
            $errors[] = $exception->getMessage();
        } catch (mysqli_sql_exception $exception) {
            removeUploadedProductImage($photo_produit);
            error_log($exception->getMessage());
            $errors[] = "Impossible d’ajouter le produit.";
        }
    }
    $feedback = '<div class="alert alert-danger">' . implode('<br>', array_map('htmlspecialchars', $errors)) . '</div>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Ajouter un produit - IT_SHOP</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<header>
    <div class="container header-content">
        <div class="logo">
            <img src="img/logo/Logo.png" alt="it_SHOP Logo" />
        </div>
        <div class="text-center flex-grow-1">
            <h5 class="mb-0">
                Bonjour <span class="text-primary">
                    <?php echo 'admin'; ?>
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
    <h2 class="mb-4 text-center">Ajouter un nouveau produit</h2>

    <!-- Affichage des messages -->
    <form method="POST" enctype="multipart/form-data" class="border p-4 rounded bg-light shadow-sm">
        <?= csrfField() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label>Nom :</label>
                <input type="text" name="nom" class="form-control" required maxlength="50" minlength="3"
                    value="<?= htmlspecialchars(postText('nom')) ?>">
            </div>
            <div class="col-md-6">
                <label>Prix (€) :</label>
                <input type="number" name="prix" step="0.01" class="form-control" required min="0.1" max="1000000"
                    value="<?= htmlspecialchars(postText('prix')) ?>"
                    onkeydown="if(event.key === 'e' || event.key === 'E' || event.key === '+' || event.key === '-') event.preventDefault();">
            </div>
            <div class="col-md-6">
                <label>Quantité en stock :</label>
                <input type="number" name="quantite_en_stock" class="form-control" required min="0" max="1000000"
                    value="<?= htmlspecialchars(postText('quantite_en_stock')) ?>"
                    onkeydown="if(event.key === 'e' || event.key === 'E'|| event.key === '+' || event.key === '-') event.preventDefault();">
            </div>
            <div class="col-md-6">
                <label>Catégorie :</label>
                <input type="text" name="categorie" class="form-control" required maxlength="50" minlength="3"
                    value="<?= htmlspecialchars(postText('categorie')) ?>">
            </div>
            <div class="col-md-6">
                <label>Marque :</label>
                <input type="text" name="marque" class="form-control" required maxlength="50" minlength="2"
                    value="<?= htmlspecialchars(postText('marque')) ?>"
                    onkeydown="if(event.key === '+' || event.key === '-') event.preventDefault();">
            </div>
            <div class="col-md-6">
                <label>Date d’ajout :</label>
                <input type="date" name="date_ajout" value="<?= date('Y-m-d') ?>" class="form-control" readonly>
            </div>
            <div class="col-md-6">
                <label>Évaluation moyenne :</label>
                <input type="number" name="evaluation_moyenne" step="0.1" min="0" max="5" class="form-control" required
                    pattern="^([0-4](\.\d)?|5(\.0)?)$"
                    title="Entrez un nombre entre 0 et 5, avec au maximum un chiffre après la virgule."
                    value="<?= htmlspecialchars(postText('evaluation_moyenne')) ?>"
                    onkeydown="if(event.key === 'e' || event.key === 'E'|| event.key === '+' || event.key === '-') event.preventDefault();">
            </div>
            <div class="col-md-6">
                <label>Statut :</label>
                <select name="statut" class="form-control" required>
                    <option value="disponible" <?= ((postText('statut')) === 'disponible') ? 'selected' : '' ?>>Disponible</option>
                    <option value="en rupture" <?= ((postText('statut')) === 'en rupture') ? 'selected' : '' ?>>En rupture</option>
                </select>
            </div>
            <div class="col-12">
                <label>Description :</label>
                <textarea name="description" rows="4" class="form-control" required minlength="3" maxlength="1000"><?= htmlspecialchars(postText('description')) ?></textarea>
            </div>
            <div class="col-md-6">
                <label>Photo du produit :</label>
                <input type="file" name="photo_produit" accept="image/*" class="form-control" required onchange="checkFileSize(this)">
                <small class="form-text text-muted">Formats acceptés : JPG, JPEG, PNG, GIF, WEBP. Max 5MB.</small>
                <div id="photo_error" class="text-danger mt-1">
                    <?php
                    // Affiche l'erreur côté serveur si l'image est trop grosse ou invalide
                    if (!empty($errors)) {
                        foreach ($errors as $err) {
                            if (strpos($err, 'image') !== false) {
                                echo htmlspecialchars($err) . "<br>";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
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

        <div class="mt-4 d-flex justify-content-between">
            <a href="admin.php" class="btn btn-secondary">← Annuler</a>
            <button type="submit" class="btn btn-success">Ajouter le produit</button>
        </div>
    </form>
</div>

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
        </div>
        <hr class="my-3 border-secondary">
        <div class="text-center text-muted py-2">
            <p class="mb-0">&copy; <?= date("Y"); ?> IT_SHOP. Tous droits réservés.</p>
        </div>
    </div>
</footer>
</body>
</html>
