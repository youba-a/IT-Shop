<?php
session_start();
require_once "connect.php";

// Vérifie que la requête est POST et que l'ID existe et est numérique
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);

    // Préparation SQL pour éviter injection
    $stmt = $connexionDB->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($produit = $result->fetch_assoc()) {
        $evaluation = round($produit['evaluation_moyenne']);
        $stars = str_repeat("⭐", $evaluation) . str_repeat("☆", 5 - $evaluation);
    } else {
        echo "<div class='container'><p class='text-danger'>Produit introuvable.</p></div>";
        exit;
    }
    $stmt->close();
} else {
    echo "<div class='container'><p class='text-danger'>Accès non autorisé ou ID invalide.</p></div>";
    exit;
}
$connexionDB->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail Produit - <?php echo htmlspecialchars($produit['nom']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<header>
  <div class="container header-content">
    <div class="logo">
      <img src="img/logo/Logo.png" alt="it_SHOP Logo">
    </div>
    <button id="menu-toggle" class="menu-toggle-button" aria-label="Menu">
      <i class="fas fa-bars"></i>
    </button>
    <nav class="main-navigation">
      <ul>
        <li><a href="index.php">ACCUEIL</a></li>
        <li><a href="produit.php">NOS PRODUITS</a></li>
        <li><a href="contact.php">CONTACT</a></li>
        <li><a href="auth.php">SE CONNECTER</a></li>
      </ul>
    </nav>
  </div>
</header>

<main>
  <section class="py-5">
    <div class="container">
      <div class="row g-4 align-items-stretch">
        <div class="col-md-6">
          <img src="img/<?php echo htmlspecialchars($produit['photo_produit']); ?>"
               alt="<?php echo htmlspecialchars($produit['nom']); ?>"
               class="img-fluid rounded shadow h-100 object-fit-cover">
        </div>
        <div class="col-md-6">
          <div class="card p-4 w-100">
            <h2><?php echo htmlspecialchars($produit['nom']); ?></h2>
            <p><strong>Description :</strong><br><?php echo nl2br(htmlspecialchars($produit['description'])); ?></p>
            <p><strong>Prix :</strong> <?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
            <p><strong>Catégorie :</strong> <?php echo htmlspecialchars($produit['categorie']); ?></p>
            <p><strong>Marque :</strong> <?php echo htmlspecialchars($produit['marque']); ?></p>
            <p><strong>Note :</strong> <?php echo $stars . ' (' . number_format($produit['evaluation_moyenne'], 1) . '/5)'; ?></p>
            <form action="produit.php" method="get">
              <button type="submit" class="btn btn-outline-primary mt-3">⬅ Retour à la boutique</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
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

<script>
  document.getElementById('menu-toggle').addEventListener('click', function () {
    document.querySelector('.main-navigation').classList.toggle('active');
  });
</script>
</body>
</html>
