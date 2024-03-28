<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("Location: connexion.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit;
}

require_once 'db.php'; // Inclure le fichier de connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $post_id = $_POST['post_id'];
    $commentaire = $_POST['commentaire'];

    // Vérifier si le commentaire n'est pas vide
    if (!empty($commentaire)) {
        // Préparer la requête d'insertion du commentaire
        $sql = "INSERT INTO commentaires (id_contenu, id_utilisateur, message, date_publication) VALUES (?, ?, ?, NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $post_id, $_SESSION['id_utilisateur'], $commentaire);

            if ($stmt->execute()) {
                header("Location: " . $_SERVER["HTTP_REFERER"]);
                exit;
            } else {
                echo "Erreur lors de l'ajout du commentaire.";
            }

            $stmt->close();
        }
    } else {
        echo "Le commentaire ne peut pas être vide.";
    }

    // Fermer la connexion à la base de données
    $conn->close();
}
