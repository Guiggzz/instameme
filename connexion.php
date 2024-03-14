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
    require_once 'header.php';
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "instameme";

    $conn = new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        die("La connexion a échoué : " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pseudo = $_POST['username'];
        $password = md5($_POST['password']);

        $sql = "SELECT * FROM utilisateurs WHERE pseudo = '$pseudo' AND mot_de_passe = '$password'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['connecte'] = True;
            $_SESSION['id_utilisateur'] = $user['id'];
            header("Location: Page_accueil.php");
        } else {
            echo "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
    ?>

    <h2>Connexion</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="username">Nom d'utilisateur:</label><br>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Mot de passe:</label><br>
        <input type="password" id="password" name="password" required><br>
        <a href="creacompte.php">Pas de compte ?</a>

        <input type="submit" value="Se connecter">
    </form>
</body>

</html>