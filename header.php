<?php
session_start();
?>

<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta charset="UTF-8">
    <title>Instameme</title>
    <link rel="shortcut icon" href="img\Instameme - Logo.png" type="image/x-icon">
</head>

<body>

    <header>
        <div id="header">
            <a href="Page_accueil.php"><img src="img/icon.png" style="width: auto; height: 80px;"></a>
            <a href="Page_accueil.php">Accueil</a>
            <form action="search.php" method="POST">
                <input name=recherche type="text" placeholder="Recherche" required style="height: 40px; width: 200px; border-radius: 5px; font-size: 1rem;border-color: rgba(0, 0, 0, 0.467);color: aliceblue;">
                <button type="submit">🔎</button>
            </form>
            <?php
            if (empty($_SESSION["connecte"])) {
                if ($_SESSION["connecte"] == True) {
                    echo '<a href="Creerpos.php">Créer</a>';
                    echo '<a href="deco.php">Deconnexion</a>';
                    echo '<a href="user.php?user_id=' . $_SESSION['id_utilisateur'] . '">Profil</a>';
                    echo isset($_SESSION['pseudo']) ? '<a>Bonjour ' . $_SESSION['pseudo'] . '</a>' : '';
                } else {
                    echo '<a href="connexion.php">Connexion</a>';
                }
            }
            ?>
        </div>
    </header>