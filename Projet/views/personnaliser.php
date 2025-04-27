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
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$voyages = json_decode(file_get_contents('../data/voyages.json'), true);
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

$personnalisations = $_SESSION['personnalisations'][$voyage_id] ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnaliser - Festival Goers</title>
    <link rel="stylesheet" href="../css/styles.css?v=<?= time(); ?>">
    <style>
        .personnaliser {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .personnaliser h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .etape-item {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 5px solid #3498db;
            transition: transform 0.2s ease;
        }
        .etape-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
        }
        .etape-item h3 {
            color: #2980b9;
            margin: 0 0 15px;
            font-size: 1.5em;
        }
        .etape-item p {
            margin: 5px 0;
            color: #7f8c8d;
        }
        .etape-item p span {
            font-weight: bold;
            color: #34495e;
        }
        .option-field {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        .option-field label {
            width: 180px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1em;
        }
        .option-field select {
            flex: 1;
            padding: 10px;
            border: 2px solid #3498db;
            border-radius: 6px;
            font-size: 1em;
            background: #ecf0f1;
            color: #2c3e50;
            transition: border-color 0.3s ease;
        }
        .option-field select:focus {
            border-color: #2980b9;
            outline: none;
            background: #fff;
        }
        .personnaliser-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }
        .btn {
            padding: 12px 25px;
            font-size: 1.1em;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-retour {
            background: #95a5a6;
            color: #fff;
        }
        .btn-retour:hover {
            background: #7f8c8d;
            transform: scale(1.05);
        }
        .btn-primary {
            background: #3498db;
            color: #fff;
        }
        .btn-primary:hover {
            background: #2980b9;
            transform: scale(1.05);
        }
    </style>
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
        <section class="personnaliser">
            <h1>Personnaliser : <?php echo htmlspecialchars($voyage['titre']); ?></h1>
            <form action="../scripts/traiter_personnalisation.php" method="POST">
                <input type="hidden" name="voyage_id" value="<?php echo $voyage_id; ?>">
                <?php foreach ($voyage['etapes'] as $index => $etape): ?>
                    <div class="etape-item">
                        <h3><?php echo htmlspecialchars($etape['titre']); ?></h3>
                        <p><span>Jour :</span> <?php echo htmlspecialchars($etape['jour'] ?? 'Non spécifié'); ?></p>
                        <p><span>Description :</span> <?php echo htmlspecialchars($etape['description'] ?? 'Non spécifiée'); ?></p>
                        <?php if (isset($etape['options']) && is_array($etape['options'])): ?>
                            <?php foreach ($etape['options'] as $key => $option): ?>
                                <div class="option-field">
                                    <label for="option_<?php echo $index . '_' . $key; ?>"><?php echo ucfirst($key); ?> :</label>
                                    <select name="options[<?php echo $index; ?>][<?php echo $key; ?>]" id="option_<?php echo $index . '_' . $key; ?>" required>
                                        <?php foreach ($option['valeurs'] as $val): ?>
                                            <?php $selected = ($personnalisations[$index][$key] ?? $option['choix'] ?? '') === $val ? 'selected' : ''; ?>
                                            <option value="<?php echo $val; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($val); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Aucune option personnalisable pour cette étape.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="personnaliser-actions">
                    <a href="voyage_details.php?id=<?php echo $voyage_id; ?>" class="btn btn-retour">Retour</a>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </section>
    </main>
    <footer>
        <p>© 2025 Festival Goers - Tous droits réservés</p>
    </footer>
</body>
</html>