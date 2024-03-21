<?php
require_once 'header.php';
$servername = "localhost";
$username = "root";
$password = "";
$database = "instameme";

$conn = new mysqli($servername, $username, $password, $database);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'];
    $image = $_FILES['image'];
    $userId = $_SESSION['id_utilisateur'];
    $imageName = $image['name'];
    $imageTmpPath = $image['tmp_name'];
    $imageDirectory = 'images/';
    $imagePath = $imageDirectory . $imageName;

    if (move_uploaded_file($imageTmpPath, $imagePath)) {
        $sql = "INSERT INTO contenus (id_utilisateur, description, chemin_image) VALUES ($userId, '$description', '$imageName')";
        $result = $conn->query($sql);

        if ($result) {
            header("Location: Page_accueil.php");
            echo 'Post créé avec succès.';
        } else {
            echo 'Erreur lors de la création du post.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'];
    $image = $_FILES['image'];
    $userId = $_SESSION['id_utilisateur'];
    $imageName = $image['name'];
    $imageTmpPath = $image['tmp_name'];
    $imagePath = 'uploads/' . $imageName;

    if (move_uploaded_file($imageTmpPath, $imagePath)) {
        $sql = "INSERT INTO contenus (id_utilisateur, description, chemin_image) VALUES ($userId, '$description', '$imageName')";
        $result = $conn->query($sql);

        if ($result) {
            header("Location: Page_accueil.php");
        } else {
            echo 'Erreur lors de la création du post.';
        }
    } else {
        echo 'Erreur lors du téléchargement de l\'image.';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Créer un Post</title>
</head>

<body>
    <h1>Créer un Post</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea><br><br>
        <label for="image">Image:</label>
        <input type="file" name="image" id="image" required><br><br>
        <input type="submit" value="Créer le Post">
    </form>
</body>

</html>