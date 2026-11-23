<?php
require_once __DIR__ . '/session.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['gerant', 'assistant'], true)) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/connect.php';

$role = $_SESSION['role'];

// En-têtes anti-cache
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Colonnes autorisées au tri
$sortable = ['id', 'nom', 'prix', 'quantite_en_stock', 'categorie', 'marque', 'date_ajout', 'evaluation_moyenne', 'statut'];

// Colonne de tri choisie (par défaut 'nom')
$orderBy = in_array($_GET['sort'] ?? '', $sortable) ? $_GET['sort'] : 'nom';

// Ordre de tri choisi (asc ou desc), par défaut asc
$order = (isset($_GET['order']) && strtolower(is_string($_GET['order']) ? $_GET['order'] : '') === 'desc') ? 'DESC' : 'ASC';

// Requête SQL avec tri dynamique
$sql = "SELECT id, nom, prix, quantite_en_stock, categorie, marque, date_ajout, evaluation_moyenne, statut
        FROM produits
        ORDER BY " . $connexionDB->real_escape_string($orderBy) . " " . $order;
$result = $connexionDB->query($sql);
if (!$result) {
    die("Erreur SQL : " . $connexionDB->error);
}

// Fonction pour déterminer le prochain ordre à appliquer (inverse de l'actuel)
function next_order($col, $currentSort, $currentOrder) {
    if ($col === $currentSort) {
        return $currentOrder === 'ASC' ? 'desc' : 'asc';
    }
    return 'asc';
}

// Fonction pour afficher une flèche indiquant le sens du tri
function arrow_sort($col, $currentSort, $currentOrder) {
    if ($col === $currentSort) {
        return $currentOrder === 'ASC' ? ' ▲' : ' ▼';
    }
    return '';
}

$currentSort = $orderBy;
$currentOrder = $order;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - it_SHOP</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
<?php if (isset($_SESSION['feedback_message'])): ?>
    <div class="container mt-3">
        <?= $_SESSION['feedback_message']; ?>
    </div>
    <?php unset($_SESSION['feedback_message']); ?>
<?php endif; ?>

<main class="container mt-4">
    <h1 class="text-center">Gestion des produits</h1>

    <?php if ($role === 'gerant'): ?>
        <div class="mb-3 text-center">
            <a href="ajouter_produit.php" class="btn btn-success">+ Ajouter un produit</a>
        </div>
    <?php endif;  ?>

    <table class="table table-striped table-bordered">
        <thead class="table-light">
        <tr>
            <th><a href="?sort=id&order=<?= next_order('id', $currentSort, $currentOrder) ?>">ID<?= arrow_sort('id', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=nom&order=<?= next_order('nom', $currentSort, $currentOrder) ?>">Nom<?= arrow_sort('nom', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=prix&order=<?= next_order('prix', $currentSort, $currentOrder) ?>">Prix (€)<?= arrow_sort('prix', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=quantite_en_stock&order=<?= next_order('quantite_en_stock', $currentSort, $currentOrder) ?>">Quantité<?= arrow_sort('quantite_en_stock', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=categorie&order=<?= next_order('categorie', $currentSort, $currentOrder) ?>">Catégorie<?= arrow_sort('categorie', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=marque&order=<?= next_order('marque', $currentSort, $currentOrder) ?>">Marque<?= arrow_sort('marque', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=date_ajout&order=<?= next_order('date_ajout', $currentSort, $currentOrder) ?>">Date d'ajout<?= arrow_sort('date_ajout', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=evaluation_moyenne&order=<?= next_order('evaluation_moyenne', $currentSort, $currentOrder) ?>">Évaluation<?= arrow_sort('evaluation_moyenne', $currentSort, $currentOrder) ?></a></th>
            <th><a href="?sort=statut&order=<?= next_order('statut', $currentSort, $currentOrder) ?>">Statut<?= arrow_sort('statut', $currentSort, $currentOrder) ?></a></th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['nom']); ?></td>
                <td><?php echo htmlspecialchars(number_format($row['prix'], 2, ',', ' ')); ?></td>
                <td><?php echo htmlspecialchars($row['quantite_en_stock']); ?></td>
                <td><?php echo htmlspecialchars($row['categorie']); ?></td>
                <td><?php echo htmlspecialchars($row['marque']); ?></td>
                <td><?php echo htmlspecialchars($row['date_ajout'] ? date('d/m/Y', strtotime($row['date_ajout'])) : 'Non défini'); ?></td>
                <td><?php echo htmlspecialchars(number_format($row['evaluation_moyenne'], 1)); ?></td>
                <td><?php echo htmlspecialchars($row['statut']); ?></td>
                <td>
                    <a href="detail_produit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-primary btn-sm">Voir</a>
                    <?php if ($role === 'gerant'): ?>
                        <a href="modifier_produit.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-warning btn-sm ms-2">Modifier</a>
                        <form method="post" action="supprimer.php" style="display:inline;"
                              onsubmit="return confirm('Voulez-vous vraiment supprimer ce produit ?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm ms-2">Supprimer</button>
                        </form>
                    <?php else: ?>
                        <a href="modifier_produit.php?id=<?php echo (int)$row['id']; ?>&assistant=1" class="btn btn-warning btn-sm ms-2">Modifier </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if ($result->num_rows === 0): ?>
            <tr>
                <td colspan="10" class="text-center">Aucun produit trouvé.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
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
        </div>
        <hr class="my-3 border-secondary">
        <div class="text-center text-muted py-2">
            <p class="mb-0">&copy; <?php echo date("Y"); ?> IT_SHOP. Tous droits réservés.</p>
        </div>
    </div>
</footer>
</body>
</html>
<?php
$result->free();
$connexionDB->close();
?>