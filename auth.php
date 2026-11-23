<?php
require_once __DIR__ . '/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Connexion Admin - it_SHOP</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="log.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body>

<header>
  <div class="container header-content">
    <div class="logo">
      <img src="img/logo/Logo.png" alt="it_SHOP Logo" />
    </div>

    <!-- Bouton menu mobile -->
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

  <main class="admin-login-container">
  <div class="login-box">
    <h2>Connexion Admin</h2>

    <?php
    if (!empty($_SESSION['error'])) {
        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    ?>

    <form method="post" action="login.php" data-parsley-validate>
            <?= csrfField() ?>
      <div class="form-group mb-3">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" class="form-control" name="username" required />
      </div>
      <div class="form-group mb-4">
        <label for="password">Mot de passe</label>
        <input type="password" class="form-control" name="password" required minlength="4" />
      </div>
      <button type="submit" class="btn btn-primary w-100">Se connecter</button>
    </form>
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

    <!-- Script Menu Mobile -->
    <script>
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.querySelector('.main-navigation').classList.toggle('active');
        });
    </script>

<script src="https://cdn.jsdelivr.net/npm/parsleyjs@2/dist/parsley.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
  window.ParsleyConfig = {
    errorsWrapper: '<div class="invalid-feedback"></div>',
    errorTemplate: '<span></span>',
    errorClass: 'is-invalid',
    successClass: 'is-valid'
  };
</script>

</body>
</html>
