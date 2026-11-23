<?php
require_once __DIR__ . '/session.php';
requireCsrf();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>it_SHOP</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="traitment_contact.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
<?php
require __DIR__ . '/vendor/autoload.php'; // Chargement automatique des bibliothèques via Composer

use Valitron\Validator;                  // Validation des données de formulaire
use PHPMailer\PHPMailer\PHPMailer;       // Classe principale pour l'envoi d'e-mails
use PHPMailer\PHPMailer\Exception;       // Classe pour la gestion des erreurs PHPMailer

// Récupération des données du formulaire, avec suppression des espaces inutiles
$data = [
    'nom'       => trim(postText('nom')),
    'email'     => trim(postText('email')),
    'sujet'     => trim(postText('sujet')),
    'telephone' => trim(postText('telephone')),
    'message'   => trim(postText('message')),
];

// Création du validateur avec les données
$v = new Validator($data);

// Définition des règles de validation
$v->rule('required', ['nom', 'email', 'sujet', 'message']);                       // Champs obligatoires
$v->rule('email', 'email');                                                     // Vérifie que l'email est valide
$v->rule('regex', 'nom', '/^[a-zA-ZÀ-ÿ \'-]+$/u')->message(
    'Le nom ne doit contenir que des lettres, espaces, apostrophes ou tirets.'
); // Expression régulière pour les noms
$v->rule('regex', 'telephone', '/^(\+?\d{1,3}[- ]?)?(\d{10,14})?$/')->message(
    'Le téléphone est invalide.'
); // Numéro de téléphone (optionnel mais formaté s'il est rempli)
$v->rule('lengthMin', 'message', 10);                                           // Le message doit faire minimum 10 caractères

// Si la validation échoue
if (!$v->validate()) {
    echo '<div class="message-container error">';
    echo "<h2>Erreurs dans le formulaire :</h2><ul>";

    // Affichage de toutes les erreurs de validation
    foreach ($v->errors() as $fieldErrors) {
        foreach ($fieldErrors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>"; // Protection XSS
        }
    }

    echo "</ul>";
    echo '<a href="contact.php">Retour au formulaire</a>';
    echo '</div>';
} else {

    // Configuration et envoi de l'e-mail avec PHPMailer
    $mail = new PHPMailer(true);

    try {
        require_once __DIR__ . '/db.php';
        $smtp = shopConfig()['smtp'];
        if ($smtp['host'] === '' || $smtp['from'] === '' || $smtp['to'] === '') {
            throw new Exception('Configuration SMTP incomplète.');
        }
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->Port = $smtp['port'];
        $mail->SMTPAuth = $smtp['user'] !== '';
        $mail->Username = $smtp['user'];
        $mail->Password = $smtp['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 10;
        $mail->setFrom($smtp['from'], 'IT Shop');
        $mail->addAddress($smtp['to']);
        $mail->addReplyTo($data['email']);

        // Contenu du mail
        $mail->isHTML(false); // Format texte brut (pas HTML)
        $mail->Subject = '[Contact] ' . $data['sujet']; // Sujet de l'email
        $mail->Body    = "Nom: {$data['nom']}\nEmail: {$data['email']}\nTéléphone: {$data['telephone']}\n\nMessage:\n{$data['message']}";

        $mail->send(); // Envoi du mail

        // Message de succès
        echo '<div class="message-container success">';
        echo "<h2>Message envoyé avec succès !</h2>";
        echo '<a href="contact.php">Retour au formulaire</a>';
        echo '</div>';

    } catch (Exception $e) {
        // Gestion des erreurs d'envoi
        echo '<div class="message-container error">';
        echo "<h2>Erreur lors de l'envoi du message :</h2>";
        echo '<p>Le service de messagerie est indisponible.</p>';
        echo '<a href="contact.php">Retour au formulaire</a>';
        echo '</div>';
    }
}
?>

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

<!-- Parsley.js CSS (facultatif pour le style des erreurs) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/parsleyjs/src/parsley.css">

<!-- Parsley.js -->
<script src="https://cdn.jsdelivr.net/npm/parsleyjs@2/dist/parsley.min.js"></script>

<script>
  // Menu responsive
const menuToggleButton = document.querySelector('.menu-toggle-button');
  const mainNavigation = document.querySelector('.main-navigation');

  if (menuToggleButton && mainNavigation) {
    menuToggleButton.addEventListener('click', () => {
      mainNavigation.classList.toggle('active');
    });
  }

  // Configuration Parsley.js (validation des formulaires)
  window.ParsleyConfig = {
    errorsWrapper: '<div class="invalid-feedback"></div>',
    errorTemplate: '<span></span>',
    errorClass: 'is-invalid',
    successClass: 'is-valid'
  };

  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[data-parsley-validate]');
    if (form) {
      $(form).parsley(); // jQuery requis par Parsley
    }
  });
</script>

<!-- jQuery (obligatoire pour Parsley) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

</body>
</html>
