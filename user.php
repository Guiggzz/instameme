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
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        $sql_post = "SELECT contenus.id, contenus.id_utilisateur, contenus.description, contenus.chemin_image, contenus.date_publication, utilisateurs.pseudo
        FROM contenus 
        INNER JOIN utilisateurs ON contenus.id_utilisateur = utilisateurs.id 
        WHERE contenus.id_utilisateur = $user_id";

        $result_post = $conn->query($sql_post);

        if ($result_post->num_rows > 0) {
            echo "<div class='grid-container'>";
            while ($row_post = $result_post->fetch_assoc()) {
                echo "<div class='grid-item'>";
                echo "<h2><a href='?user_id={$row_post['id_utilisateur']}'>" . $row_post["pseudo"] . "</a></h2>";
                echo "<img onclick=\"location.href='view_comments.php?post_id=" . $row_post['id'] . "'\" src='images/" . $row_post["chemin_image"] . "' alt='Image du post'>";
                echo "<p>" . $row_post["description"] . "</p>";

                $sql_likes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_contenu = " . $row_post['id'];
                $likes_result = $conn->query($sql_likes);
                if ($likes_result->num_rows > 0) {
                    $likes_row = $likes_result->fetch_assoc();
                    $like_count = $likes_row['like_count'];
                    echo "<p><b>&#x2661; " . $like_count . "</b></p>";
                } else {
                    echo "<p>Pas de likes pour ce post.</p>";
                }

                $sql_com = "SELECT commentaires.message, utilisateurs.pseudo AS user_pseudo 
                FROM commentaires 
                INNER JOIN utilisateurs ON commentaires.id_utilisateur = utilisateurs.id 
                WHERE commentaires.id_contenu =" . $row_post['id'] . " LIMIT 3;";
                $com_result = $conn->query($sql_com);
                if ($com_result->num_rows > 0) {
                    while ($com_row = $com_result->fetch_assoc()) {
                        echo "<p><strong>" . $com_row['user_pseudo'] . ":</strong> " . $com_row['message'] . "</p>";
                    }
                    // Create a button to view all comments
                    echo "<button onclick=\"location.href='view_comments.php?post_id=" . $row_post['id'] . "'\">Voir tous les commentaires</button>";
                } else {
                    echo "<p>Pas de commentaires.</p>";
                }
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "Aucun post trouvé pour cet utilisateur.";
        }
    } else {
        echo "Aucun identifiant d'utilisateur spécifié.";
    }

    ?>
</body>

</html>