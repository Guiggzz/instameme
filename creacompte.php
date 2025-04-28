use function date;
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
</head>

<body>
    <?php
    require_once 'header.php';
    require_once 'db.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $pseudo = $_POST['username'];
        $password = md5($_POST['password']);
        $dateinscription = date('Y-m-d H:i:s');

        $sql_check = "SELECT * FROM utilisateurs WHERE pseudo='$pseudo'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows > 0) {
            echo "Ce nom d'utilisateur existe déjà.";
        } else {
            $sql_insert = "INSERT INTO utilisateurs(pseudo, mot_de_passe, date_inscription) VALUES ('$pseudo', '$password', '$dateinscription')";
            if ($conn->query($sql_insert) === TRUE) {
                $_SESSION['connecte'] = True;
                header("Location: index.php");
            } else {
                echo "Erreur lors de la création du compte : " . $conn->error;
            }
        }
    }
    ?>

    <h2>Créer un compte</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="username">Nom d'utilisateur:</label><br>
        <input type="text" id="username" name="username" required><br>

        <label for="password">Mot de passe:</label><br>
        <input type="password" id="password" name="password" required><br>

        <input type="submit" value="Créer un compte">
    </form>
</body>

</html>