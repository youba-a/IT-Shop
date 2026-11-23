<?php
require_once __DIR__ . '/session.php';

// Vérifie que l'utilisateur est connecté
if (!in_array($_SESSION['role'] ?? null, ['gerant', 'assistant'], true)) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/connect.php';

$role = $_SESSION['role'];

// Récupérer l'ID produit depuis GET et valider
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Produit invalide.");
}

$id = (int)$_GET['id'];

// Préparer et exécuter la requête pour récupérer le produit
$sql = "SELECT * FROM produits WHERE id = ?";
$stmt = $connexionDB->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$produit = $result->fetch_assoc();

if (!$produit) {
    die("Produit introuvable.");
}

// Génération d’étoiles pour la note (arrondi à l'entier)
$note = round($produit['evaluation_moyenne']);
$note = max(0, min(5, $note)); // s'assurer que la note soit entre 0 et 5
$etoiles = str_repeat('⭐', $note) . str_repeat('☆', 5 - $note);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Admin - it_SHOP</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
                    <?php echo ($role == 'gerant') ? 'admin' : 'assistant'; ?>
                </span>
            </h5>
        </div>
        <nav class="main-navigation">
            <ul>
                <li><a href="index.php">ACCUEIL</a></li>
                <li><a href="admin.php"> Admin</a></li>
                <li><form method="post" action="logout.php"><?= csrfField() ?><button type="submit" class="btn btn-link">Déconnecter</button></form></li>
            </ul>
        </nav>
    </div>
</header>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php unset($_SESSION['success_message']); ?>

    <?php endif; ?>
<main class="container my-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-white text-dark text-center rounded-top-4 py-3">
            <h2 class="mb-0">Détail du produit : <?= htmlspecialchars($produit['nom']) ?></h2>
        </div>
        <div class="card-body p-4">
            <div class="row g-4 align-items-center justify-content-center">

                <!-- Image du produit -->
                <div class="col-md-5 text-center">
                    <?php if (!empty($produit['photo_produit'])): ?>
                        <img src="img/<?= htmlspecialchars($produit['photo_produit']) ?>"
                             alt="Photo de <?= htmlspecialchars($produit['nom']) ?>"
                             class="img-fluid rounded border shadow-sm" style="max-width: 100%;"/>
                    <?php else: ?>
                        <div class="text-muted"><em>Aucune image disponible</em></div>
                    <?php endif; ?>
                </div>

                <!-- Détails -->
                <div class="col-md-7">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>ID :</strong> <?= htmlspecialchars($produit['id']) ?></li>
                        <li class="list-group-item"><strong>Nom :</strong> <?= htmlspecialchars($produit['nom']) ?></li>
                        <li class="list-group-item"><strong>Description :</strong><br><?= nl2br(htmlspecialchars($produit['description'])) ?></li>
                        <li class="list-group-item"><strong>Prix :</strong> <?= number_format($produit['prix'], 2, ',', ' ') ?> €</li>
                        <li class="list-group-item"><strong>Quantité en stock :</strong> <?= htmlspecialchars($produit['quantite_en_stock']) ?></li>
                        <li class="list-group-item"><strong>Catégorie :</strong> <?= htmlspecialchars($produit['categorie']) ?></li>
                        <li class="list-group-item"><strong>Marque :</strong> <?= htmlspecialchars($produit['marque']) ?></li>
                        <li class="list-group-item"><strong>Date d’ajout :</strong> <?= htmlspecialchars($produit['date_ajout']) ?></li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>Évaluation moyenne :</strong>
                            <span class="fs-5"><?= $etoiles ?> <small class="text-muted">(<?= number_format($produit['evaluation_moyenne'], 1) ?>/5)</small></span>
                        </li>
                        <li class="list-group-item"><strong>Statut :</strong> <?= htmlspecialchars($produit['statut']) ?></li>
                    </ul>

                    <!-- Boutons -->
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="admin.php" class="btn btn-outline-secondary">
                            ← Retour à l'administration
                        </a>
                        <?php if ($role === 'gerant' || $role === 'assistant'): ?>
                            <a href="modifier_produit.php?id=<?= $produit['id'] ?>" class="btn btn-primary">Modifier ce produit</a>

                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

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
</body>
</html>
