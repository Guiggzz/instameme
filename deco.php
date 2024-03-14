<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
</head>

<body>
    <?php
    session_start();
    $_SESSION['connecte'] = False;
    header("Location: Page_accueil.php");
    ?>
</body>

</html>