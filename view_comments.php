<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post</title>
</head>

<body>
    <?php
    require_once 'header.php';
    session_start();
    require_once 'db.php';
    if (isset($_GET['post_id']) && !empty($_GET['post_id'])) {
        $post_id = $_GET['post_id'];

        $sql_post = "SELECT contenus.*, utilisateurs.pseudo AS user_pseudo
                 FROM contenus 
                 INNER JOIN utilisateurs ON contenus.id_utilisateur = utilisateurs.id
                 WHERE contenus.id = $post_id";
        $result_post = $conn->query($sql_post);

        if ($result_post->num_rows == 1) {
            $row_post = $result_post->fetch_assoc();

            echo "<div class='post-container'>";
            echo "<img src='images/" . $row_post["chemin_image"] . "' alt='Image du post'>";
            echo "<div class='post-details'>";
            echo "<h2>" . $row_post["user_pseudo"] . "</h2>";
            echo "<p>" . $row_post["description"] . "</p>";

            // Afficher le nombre de likes pour ce post
            $sql_likes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_contenu = $post_id";
            $likes_result = $conn->query($sql_likes);
            if ($likes_result->num_rows > 0) {
                $likes_row = $likes_result->fetch_assoc();
                $like_count = $likes_row['like_count'];
                echo "<p><b>&#x2661; " . $like_count . "</b></p>";
            } else {
                echo "<p>Pas de likes pour ce post.</p>";
            }

            // Récupérer et afficher tous les commentaires associés à ce post dans une boîte de défilement
            $sql_comments = "SELECT commentaires.message, utilisateurs.pseudo AS user_pseudo 
                         FROM commentaires 
                         INNER JOIN utilisateurs ON commentaires.id_utilisateur = utilisateurs.id 
                         WHERE commentaires.id_contenu = $post_id";
            $result_comments = $conn->query($sql_comments);
            if ($result_comments->num_rows > 0) {
                echo "<h3>Commentaires</h3>";
                echo "<div class='comment-box'>";
                while ($row_comment = $result_comments->fetch_assoc()) {
                    echo "<div class='comment'>";
                    echo "<strong>" . $row_comment['user_pseudo'] . ":</strong> " . $row_comment['message'];
                    echo "</div>";
                }
                echo "</div>"; // Fermeture de la boîte de commentaires
            } else {
                echo "<p>Pas de commentaires.</p>";
            }
            echo "<form method='post' action='creacom.php'>";
            echo "<input type='hidden' name='post_id' value='" . $row_post['id'] . "'>";
            echo "<textarea name='commentaire' placeholder='Ajouter un commentaire'></textarea>";
            echo "<input type='submit' value='Envoyer'>";
            echo "</form>";
            echo "</div>"; // Fermeture de post-details
            echo "</div>"; // Fermeture de post-container
        } else {
            echo "Aucun post trouvé.";
        }
    } else {
        echo "Aucun identifiant de post spécifié.";
    }

    ?>
</body>

</html>