<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>InstaMeme</title>
  <style>
    /* Style pour la mise en forme de la grille */
    .grid-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      grid-gap: 20px;
    }

    .grid-item {
      border: 1px solid #ccc;
      padding: 20px;
      text-align: center;
    }
  </style>
</head>

<body>
  <?php
  require_once 'header.php'; // Si header.php contient votre en-tête de page, sinon, vous pouvez le remplacer par son contenu directement.

  $servername = "localhost";
  $username = "root";
  $password = "";
  $database = "instameme";

  // Créer une connexion
  $conn = new mysqli($servername, $username, $password, $database);

  if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
  }

  $sql_posts = "SELECT contenus.id, contenus.id_utilisateur, contenus.description, contenus.chemin_image, contenus.date_publication, utilisateurs.pseudo
              FROM contenus 
              INNER JOIN utilisateurs ON contenus.id_utilisateur = utilisateurs.id LIMIT 9";

  $result_posts = $conn->query($sql_posts);

  if (!$result_posts) {
    die("Erreur lors de l'exécution de la requête SQL : " . mysqli_error($conn));
  }

  if ($result_posts->num_rows > 0) {
    echo "<div class='grid-container'>";
    $count = 0;
    foreach ($result_posts as $row_post) {
      echo "<div class='grid-item'>";
      echo "<h2>" . $row_post["pseudo"] . "</h2>";
      echo "<img onclick=\"location.href='view_comments.php?post_id=" . $row_post['id'] . "'\"src='images/" . $row_post["chemin_image"] . "' alt='Image du post'>";

      $sql_likes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_contenu =" . $row_post['id'] . ";";
      $likes_result = $conn->query($sql_likes);
      $likes_row = $likes_result->fetch_assoc();
      $like_count = $likes_row['like_count'];
      echo "<p>Likes: " . $like_count . "</p>";

      echo "<h2>" . $row_post["description"] . "</h2>";

      $sql_nbcom = "SELECT COUNT(*) AS com_count FROM commentaires WHERE id_contenu =" . $row_post['id'] . ";";
      $nbcom_result = $conn->query($sql_nbcom);
      $nbcom_row = $nbcom_result->fetch_assoc();
      $nbcom_count = $nbcom_row['com_count'];
      echo "<p><b>" . $nbcom_count . "</b> Commentaires : </p>";

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
      $count++;
    }
    echo "</div>";
  } else {
    echo "Aucun post trouvé.";
  }
  ?>
</body>

</html>