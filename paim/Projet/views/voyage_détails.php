<?php
session_start();

// Charger les utilisateurs pour déterminer $user
$user = null;
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('../data/utilisateurs.json'); // Chemin corrigé pour index.php
    $utilisateurs = $utilisateurs_json ? json_decode($utilisateurs_json, true) : [];
    if (is_array($utilisateurs)) {
        foreach ($utilisateurs as $utilisateur) {
            if ($utilisateur['username'] === $_SESSION['user_id']) {
                $user = $utilisateur;
                break;
            }
        }
    }
}

$voyages = json_decode(file_get_contents('../data/voyages.json'), true);
$utilisateurs = json_decode(file_get_contents('../data/utilisateurs.json'), true);
$voyage_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$voyage = null;
foreach ($voyages as $v) {
    if ($v['id'] === $voyage_id) {
        $voyage = $v;
        break;
    }
}
if (!$voyage) {
    header("Location: recherche.php");
    exit();
}

$is_banned = false;
$is_reserved = false;
if (isset($_SESSION['user_id'])) {
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['username'] === $_SESSION['user_id']) {
            if (isset($utilisateur['statut']) && $utilisateur['statut'] === 'banned') {
                $is_banned = true;
            }
            if (isset($utilisateur['voyages']) && is_array($utilisateur['voyages'])) {
                foreach ($utilisateur['voyages'] as $voyage_data) {
                    $reserved_id = is_array($voyage_data) ? ($voyage_data['id'] ?? null) : $voyage_data;
                    if ($reserved_id === $voyage_id) {
                        $is_reserved = true;
                        break;
                    }
                }
            }
            break;
        }
    }
}

$updated = isset($_GET['updated']) && $_GET['updated'] == 1;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du voyage - Festival Goers</title>
    <link rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo"><a href="../index.php">Festival Goers</a></div>
            <ul class="nav-links">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="presentation.php">Présentation</a></li>
                <li><a href="recherche.php">Recherche</a></li>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <li><a href="inscription.php">Inscription</a></li>
                    <li><a href="connexion.php">Connexion</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profil.php">Profil</a></li>
                <?php endif; ?>
                <?php if (isset($user['role']) && $user['role'] === 'admin'): ?>
                    <li><a href="admin.php">Admin</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main>
        <?php if ($is_banned): ?>
            <div class="alert alert-banned">
                <p><strong>Attention :</strong> Votre compte est banni. Vous ne pouvez pas effectuer d'actions sur les voyages.</p>
            </div>
        <?php endif; ?>
        <?php if ($updated): ?>
            <div class="alert alert-success">
                <p><strong>Succès :</strong> Vos personnalisations ont été enregistrées.</p>
            </div>
        <?php endif; ?>

        <section class="voyage-details">
            <h1><?php echo isset($voyage['titre']) ? htmlspecialchars($voyage['titre']) : 'Voyage sans titre'; ?></h1>
            <div class="voyage-header">
                <img src="<?php echo htmlspecialchars($voyage['etapes'][0]['image'] ?? 'https://via.placeholder.com/720x405'); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                <div class="voyage-summary">
                    <p><span>Lieu :</span> <?php echo isset($voyage['lieu']) ? htmlspecialchars($voyage['lieu']) : 'Lieu non spécifié'; ?></p>
                    <p>

                <span>Dates :</span> 
                        <?php 
                        if (isset($voyage['dates'])) {
                            if (is_array($voyage['dates'])) {
                                echo htmlspecialchars(implode(' - ', $voyage['dates']));
                            } else {
                                echo htmlspecialchars($voyage['dates']);
                            }
                        } else {
                            echo 'Dates non spécifiées';
                        }
                        ?>
                    </p>
                    <p><span>Prix total :</span> <?php echo isset($voyage['prix_total']) ? htmlspecialchars($voyage['prix_total']) : 'Prix non spécifié'; ?> €</p>
                </div>
            </div>
            <h2>Étapes du voyage</h2>
            <div class="etapes-list">
                <?php if (isset($voyage['etapes']) && is_array($voyage['etapes'])): ?>
                    <?php foreach ($voyage['etapes'] as $index => $etape): ?>
                        <div class="etape-item">
                            <h3><?php echo isset($etape['titre']) ? htmlspecialchars($etape['titre']) : 'Étape sans titre'; ?></h3>
                            <p><span>Jour :</span> <?php echo isset($etape['jour']) ? htmlspecialchars($etape['jour']) : 'Jour non spécifié'; ?></p>
                            <p><span>Description :</span> <?php echo isset($etape['description']) ? htmlspecialchars($etape['description']) : 'Description non spécifiée'; ?></p>
                            <?php if (isset($etape['options']) && is_array($etape['options'])): ?>
                                <?php foreach ($etape['options'] as $key => $option): ?>
                                    <?php
                                    $choix = $_SESSION['personnalisations'][$voyage_id][$index][$key] ?? $option['choix'] ?? 'Non spécifié';
                                    ?>
                                    <p><span><?php echo ucfirst($key); ?> :</span> <?php echo htmlspecialchars($choix); ?></p>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune étape disponible pour ce voyage.</p>
                <?php endif; ?>
            </div>
            <h2>Options personnalisables</h2>
            <div class="options-list">
                <?php if (isset($voyage['options']) && is_array($voyage['options'])): ?>
                    <?php foreach ($voyage['options'] as $option): ?>
                        <p><?php echo htmlspecialchars($option); ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune option disponible.</p>
                <?php endif; ?>
            </div>
            <div class="voyage-actions">
                <a href="recherche.php" class="btn btn-retour">Retour</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($is_banned): ?>
                        <span class="btn btn-primary" style="background-color: #e74c3c; cursor: not-allowed;">Compte banni - Action impossible</span>
                    <?php elseif ($is_reserved): ?>
                        <span class="btn btn-primary" style="background-color: #95a5a6; cursor: not-allowed;">Voyage réservé</span>
                        <a href="recapitulatif.php?id=<?php echo $voyage_id; ?>" class="btn btn-primary">Voir le récapitulatif</a>
                    <?php else: ?>
                        <a href="personnaliser.php?id=<?php echo $voyage_id; ?>" class="btn btn-primary">Personnaliser</a>
                        <a href="../scripts/reserver_voyage.php?id=<?php echo $voyage_id; ?>" class="btn btn-primary">Réserver</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="connexion.php" class="btn btn-primary">Connectez-vous pour personnaliser</a>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
</body>
</html>