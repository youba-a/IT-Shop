<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>it_SHOP</title>
    <link rel="stylesheet" href="style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- FontAwesome pour les icônes (menu, réseaux sociaux...) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>

<body class="homepage">

    <header id="main-header">
        <div class="container header-content">
            <!-- Logo de la boutique -->
            <div class="logo">
                <img src="img/logo/Logo.png" alt="Logo IT_SHOP" />
            </div>

            <!-- Bouton pour afficher/masquer le menu sur mobile -->
            <button id="menu-toggle" class="menu-toggle-button" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Menu principal -->
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
        <!-- Section de bienvenue avec image de fond -->
        <section class="hero">
            <img src="img/women-bg.jpg" alt="background" class="bg-image">
            <div class="container text-white text-center py-5">
                <h1 class="main-title mb-3">Bienvenue chez IT_SHOP</h1>
                <p class="lead-text">
                    Votre destination unique pour des articles de qualité supérieure !<br><br>
                    Explorez notre catalogue et découvrez des produits alliant performance, durabilité et design.
                </p>
                <div class="mt-4">
                    <a href="produit.php" class="btn btn-primary btn-md">Découvrir nos Produits</a>
                </div>
            </div>
        </section>

        <!-- Section produits phares -->
        <section id="featured-products" class="py-5">
            <div class="container text-center">
                <h2 class="section-title mb-4">Nos Produits Phares</h2>
                <p class="section-lead-box">
                    Découvrez notre sélection des meilleurs articles du moment.
                </p>

                <div class="product-box-frame">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php
                        // Connexion DB + récupération des produits les mieux notés dispo
                        require_once "connect.php";
                        $sql = "SELECT id, nom, prix, evaluation_moyenne, photo_produit FROM produits WHERE statut='disponible' ORDER BY evaluation_moyenne DESC LIMIT 6";
                        $result = $connexionDB->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Création des étoiles selon la note moyenne
                                $evaluation = round($row['evaluation_moyenne']);
                                $stars = '';
                                for ($i = 0; $i < 5; $i++) {
                                    $stars .= ($i < $evaluation) ? '⭐' : '☆';
                                }
                                $stars .= ' (' . number_format($row['evaluation_moyenne'], 1) . '/5)';
                        ?>
                                <div class="col">
                                    <div class="card h-100 shadow-sm">
                                        <h5 class="card-title text-center py-3"><?php echo htmlspecialchars($row['nom']); ?></h5>
                                        <img src="img/<?php echo htmlspecialchars($row['photo_produit']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['nom']); ?>">
                                        <div class="card-body d-flex flex-column">
                                            <p class="card-text text-muted mb-2">Prix: <?php echo number_format($row['prix'], 2, ',', ' '); ?> €</p>
                                            <p class="card-text mb-3">Note: <?php echo $stars; ?></p>
                                            <form method="post" action="detail.php" class="d-inline">
                                                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                                <button type="submit" class="btn btn-outline-primary">Voir plus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "<p class='text-center col-12'>Aucun produit phare à afficher pour le moment.</p>";
                        }
                        $connexionDB->close();
                        ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

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

                <!-- Coordonnées -->
                <div class="col-md-3 mb-4">
                    <h5 class="text-uppercase mb-4">Contactez-nous</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> <a href="contact.php" class="text-decoration-none">Formulaire de contact</a></li>
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

            <div class="text-center text-muted py-2">
                <!-- Affiche automatiquement l'année actuelle -->
                <p class="mb-0">&copy; <?php echo date("Y"); ?> IT_SHOP. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- ====== SCRIPT POUR LE MENU BURGER ====== -->
    <script>
        // Toggle de la classe "active" pour afficher ou cacher le menu
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.querySelector('.main-navigation').classList.toggle('active');
        });
    </script>
</body>
</html>
