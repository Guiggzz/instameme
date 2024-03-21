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

// Vérifier si l'utilisateur est connecté
session_start();
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur connecté
}

// Traitement des actions POST (like et commentaire)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['like_post'])) {
    if (isset($user_id)) { // Vérifier si l'utilisateur est connecté
      $post_id = $_POST['post_id'];
      $sql_like = "INSERT INTO likes (id_contenu, id_utilisateur) VALUES ('$post_id', '$user_id')";
      if ($conn->query($sql_like) === TRUE) {
        // Like ajouté avec succès
        echo "Post liké avec succès.";
      } else {
        echo "Erreur lors du like : " . $conn->error;
      }
    } else {
      echo "Vous devez être connecté pour liker un post.";
    }
  }
}

// Pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$postsPerPage = 9;
$offset = ($currentPage - 1) * $postsPerPage;

$sql_posts_count = "SELECT COUNT(*) AS total_posts FROM contenus";
$result_count = $conn->query($sql_posts_count);
$total_posts_row = $result_count->fetch_assoc();
$totalPosts = $total_posts_row['total_posts'];
$totalPages = ceil($totalPosts / $postsPerPage);

$sql_posts = "SELECT contenus.id, contenus.id_utilisateur, contenus.description, contenus.chemin_image, contenus.date_publication, utilisateurs.pseudo
              FROM contenus 
              INNER JOIN utilisateurs ON contenus.id_utilisateur = utilisateurs.id 
              LIMIT $offset, $postsPerPage";

$result_posts = $conn->query($sql_posts);

if (!$result_posts) {
  die("Erreur lors de l'exécution de la requête SQL : " . mysqli_error($conn));
}

if ($result_posts->num_rows > 0) {
  echo "<div class='grid-container'>";
  while ($row_post = $result_posts->fetch_assoc()) {
    echo "<div class='grid-item'>";
    echo "<h2 onclick=\"location.href='user.php?user_id=" . $row_post['id_utilisateur'] . "'\">" . $row_post["pseudo"] . "</h2>";
    echo "<img onclick=\"location.href='view_comments.php?post_id=" . $row_post['id'] . "'\"src='images/" . $row_post["chemin_image"] . "' alt='Image du post'>";

    $sql_likes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_contenu =" . $row_post['id'] . ";";
    $likes_result = $conn->query($sql_likes);
    $likes_row = $likes_result->fetch_assoc();
    $like_count = $likes_row['like_count'];
    echo "<form method='post' action=''>";
    echo "<input type='hidden' name='post_id' value='" . $row_post['id'] . "'>";
    echo "<button type='submit' name='like_post'>Like</button>";
    echo "<span>&#x2661; " . $like_count . "</span>";
    echo "</form>";

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
      echo "<form method='post' action='creacom.php'>";
      echo "<input type='hidden' name='post_id' value='" . $row_post['id'] . "'>";
      echo "<textarea name='commentaire' placeholder='Ajouter un commentaire'></textarea>";
      echo "<input type='submit' value='Envoyer'>";
      echo "</form>";
      echo "<button onclick=\"location.href='view_comments.php?post_id=" . $row_post['id'] . "'\">Voir tous les commentaires</button>";
    } else {
      echo "<p>Pas de commentaires.</p>";
    }

    echo "</div>";
  }
  echo "</div>";

  // Affichage de la pagination
  echo "<div class='pagination'>";
  for ($i = 1; $i <= $totalPages; $i++) {
    echo "<a href='?page=$i'>$i</a>";
  }
  echo "</div>";
} else {
  echo "Aucun post trouvé.";
}

// Fermeture de la connexion
$conn->close();
