<?php
session_start();

// Charger les utilisateurs pour déterminer $user
$user = null;
if (isset($_SESSION['user_id'])) {
    $utilisateurs_json = file_get_contents('../data/utilisateurs.json');
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
    <!-- Script inline pour styles de base -->
    <script>
        (function() {
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
                return null;
            }
            const theme = getCookie('theme') || 'dark';
            const style = document.createElement('style');
            style.textContent = theme === 'dark'
                ? 'body { background-color: #1a1a1a; color: #e8eaed; visibility: hidden; }'
                : 'body { background-color: #f0f0f0; color: #333; visibility: hidden; }';
            document.head.appendChild(style);
            // Fallback : charger le CSS si main.js échoue
            setTimeout(() => {
                const cssLink = document.getElementById('theme-css');
                if (!cssLink.href) {
                    cssLink.href = `../css/${theme === 'dark' ? 'styles' : 'light'}.css?v=${new Date().getTime()}`;
                }
            }, 1000);
        })();
    </script>
    <!-- Charger le CSS via main.js -->
    <link id="theme-css" rel="stylesheet" href="">
    <script src="../scripts/main.js" defer></script>
    <script src="../scripts/script.js"></script>
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
        <button id="theme-switch">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eAeD"><path d="M480-120q-150 0-255-105T120-480q0-150 105-255t255-105q14 0 27.5 1t26.5 3q-41 29-65.5 75.5T444-660q0 90 63 153t153 63q55 0 101-24.5t75-65.5q2 13 3 26.5t1 27.5q0 150-105 255T480-120Z"/></svg>
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eAeD"><path d="M480-280q-83 0-141.5-58.5T280-480q0-83 58.5-141.5T480-680q83 0 141.5 58.5T680-480q0 83-58.5 141.5T480-280ZM200-440H40v-80h160v80Zm720 0H760v-80h160v80ZM440-760v-160h80v160h-80Zm0 720v-160h80v160h-80ZM256-650l-101-97 57-59 96 100-52 56Zm492 496-97-101 53-55 101 97-57 59Zm-98-550 97-101 59 57-100 96-56-52ZM154-212l101-97 55 53-97 101-59-57Z"/></svg>
        </button>
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
    <script>
        // Afficher le contenu après chargement
        window.addEventListener('load', () => {
            document.body.style.visibility = 'visible';
        });
    </script>
</body>
</html>