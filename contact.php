<?php
require_once __DIR__ . '/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>it_SHOP - Contact</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- ParsleyJS pour la validation côté client -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/parsleyjs/src/parsley.css">
</head>

<body>
<!-- ===================== HEADER ===================== -->
<header>
  <div class="container header-content d-flex align-items-center justify-content-between py-3">
    <div class="logo">
      <img src="img/logo/Logo.png" alt="it_SHOP Logo" />
    </div>

    <!-- Bouton pour le menu en version mobile -->
    <button id="menu-toggle" class="menu-toggle-button" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Menu de navigation -->
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

<!-- ===================== MAIN ======================= -->
<main>
  <section class="py-5">
    <div class="container">
      <!-- Titre et sous-titre -->
      <div class="section-lead-box text-center mb-4">
        <h2>Contactez-nous</h2>
        <p>Une question, une demande ou un retour ? Nous sommes à votre écoute !</p>
      </div>

      <!-- Formulaire de contact -->
      <div class="row justify-content-center">
        <div class="col-md-8">
          <form method="post" action="traitement_contact.php"
                class="card p-4 shadow-sm"
                data-parsley-validate>
            <?= csrfField() ?>

            <!-- Champ Nom -->
            <div class="mb-3">
              <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nom" name="nom"
                     placeholder="Votre nom complet" required
                     data-parsley-pattern="^[A-Za-zÀ-ÿ\s'-]+$"
                     data-parsley-pattern-message="Le nom ne doit contenir que des lettres, espaces, apostrophes ou tirets.">
            </div>

            <!-- Champ Email -->
            <div class="mb-3">
              <label for="email" class="form-label">Adresse e-mail <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email"
                     placeholder="exemple@domaine.com" required
                     data-parsley-type="email"
                     data-parsley-type-message="Veuillez entrer une adresse e-mail valide.">
            </div>

            <!-- Champ Sujet -->
            <div class="mb-3">
              <label for="sujet" class="form-label">Sujet <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="sujet" name="sujet"
                     placeholder="Sujet de votre message" required
                     data-parsley-pattern="^[A-Za-zÀ-ÿ0-9\s.,'’?!-]{5,100}$"
                     data-parsley-pattern-message="Le sujet ne doit contenir que des lettres, chiffres et ponctuations simples.">
            </div>

            <!-- Champ Téléphone (facultatif) -->
            <div class="mb-3">
              <label for="telephone" class="form-label">Téléphone</label>
              <input type="tel" class="form-control" id="telephone" name="telephone"
                     placeholder="Ex : 0612345678"
                     data-parsley-pattern="^0[1-9][0-9]{8}$"
                     data-parsley-pattern-message="Veuillez entrer un numéro français valide sans espaces (ex : 0612345678).">
            </div>

            <!-- Champ Message -->
            <div class="mb-3">
              <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
              <textarea class="form-control" id="message" name="message" rows="6"
                        placeholder="Écrivez votre message ici..." required
                        data-parsley-minlength="10"
                        data-parsley-minlength-message="Votre message doit contenir au moins 10 caractères."></textarea>
            </div>

            <!-- Bouton d'envoi -->
            <div class="text-center">
              <button type="submit" class="btn btn-primary">Envoyer le message</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>
<!-- ==================== FOOTER ====================== -->
<footer>
  <div class="container py-3">
    <div class="row">
      <!-- Liens de navigation -->
      <div class="col-md-2 mb-4">
        <h5 class="text-uppercase mb-4">Navigation</h5>
        <ul class="list-unstyled">
          <li><a href="index.php" class="text-decoration-none">Accueil</a></li>
          <li><a href="produit.php" class="text-decoration-none">Nos Produits</a></li>
          <li><a href="contact.php" class="text-decoration-none">Contact</a></li>
          <li><a href="auth.php" class="text-decoration-none">Se connecter</a></li>
        </ul>
      </div>

      <!-- Informations de contact -->
      <div class="col-md-3 mb-4">
        <h5 class="text-uppercase mb-4">Contactez-nous</h5>
        <ul class="list-unstyled">
          <li><i class="fas fa-envelope me-2"></i><a href="contact.php" class="text-decoration-none">Formulaire de contact</a></li>
        </ul>
      </div>

      <!-- Réseaux sociaux -->
      <div class="col-md-3">
        <h5 class="text-uppercase mb-4">Suivez-nous</h5>
        <div class="social-links d-flex">
          <a href="#" target="_blank" class="text-muted me-3"><i class="fab fa-facebook-f fa-2x"></i></a>
          <a href="#" target="_blank" class="text-muted me-3"><i class="fab fa-twitter fa-2x"></i></a>
          <a href="#" target="_blank" class="text-muted"><i class="fab fa-instagram fa-2x"></i></a>
        </div>
      </div>
    </div>

    <hr class="my-3 border-secondary">

    <!-- Copyright -->
    <div class="text-center text-muted py-2">
      <p class="mb-0">&copy; <?php echo date("Y"); ?> IT_SHOP. Tous droits réservés.</p>
    </div>
  </div>
</footer>

<!-- =================== SCRIPTS ====================== -->

<!-- jQuery requis par Parsley -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Parsley pour validation des champs -->
<script src="https://cdn.jsdelivr.net/npm/parsleyjs@2/dist/parsley.min.js"></script>

<script>
  // Menu mobile toggle
  document.getElementById('menu-toggle').addEventListener('click', function () {
    document.querySelector('.main-navigation').classList.toggle('active');
  });

  // Initialisation de Parsley pour le formulaire
  $(function() {
    $('form[data-parsley-validate]').parsley({
      errorsWrapper: '<div class="invalid-feedback"></div>',
      errorTemplate: '<span></span>',
      errorClass: 'is-invalid',
      successClass: 'is-valid'
    });
  });
</script>

</body>
</html>
