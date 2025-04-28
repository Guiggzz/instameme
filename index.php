<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

require_once 'db.php';

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$user_id = isset($_SESSION['id_utilisateur']) ? $_SESSION['id_utilisateur'] : 0;

if (isset($_POST['like']) && $user_id > 0) {
  $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

  if ($post_id > 0) {
    $check_like = "SELECT * FROM likes WHERE id_contenu = ? AND id_utilisateur = ?";
    $stmt = $conn->prepare($check_like);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $like_result = $stmt->get_result();

    if ($like_result->num_rows > 0) {
      $delete_like = "DELETE FROM likes WHERE id_contenu = ? AND id_utilisateur = ?";
      $stmt_delete = $conn->prepare($delete_like);
      $stmt_delete->bind_param("ii", $post_id, $user_id);
      $stmt_delete->execute();
      $stmt_delete->close();
    } else {
      $add_like = "INSERT INTO likes (id_contenu, id_utilisateur) VALUES (?, ?)";
      $stmt_add = $conn->prepare($add_like);
      $stmt_add->bind_param("ii", $post_id, $user_id);
      $stmt_add->execute();
      $stmt_add->close();
    }
    $stmt->close();

    header("Location: index.php?page=$currentPage");
    exit();
  }
}

$postsPerPage = 9;
$offset = ($currentPage - 1) * $postsPerPage;

$sql_posts_count = "SELECT COUNT(*) AS total_posts FROM contenus";
$result_count = $conn->query($sql_posts_count);
$total_posts_row = $result_count->fetch_assoc();
$totalPosts = $total_posts_row['total_posts'];
$totalPages = ceil($totalPosts / $postsPerPage);

$sql_posts = "SELECT contenus.id, contenus.id_utilisateur, contenus.description, contenus.chemin_image, 
              contenus.date_publication, utilisateurs.pseudo
              FROM contenus 
              INNER JOIN utilisateurs ON contenus.id_utilisateur = utilisateurs.id 
              ORDER BY contenus.date_publication DESC
              LIMIT $offset, $postsPerPage";

$result_posts = $conn->query($sql_posts);

if (!$result_posts) {
  die("Erreur lors de l'exécution de la requête SQL : " . mysqli_error($conn));
}

require_once 'header.php';
?>

<div class="bg-gray-50 min-h-screen pb-10">
  <div class="container mx-auto px-4 py-6 max-w-7xl">
    <?php if ($result_posts->num_rows > 0): ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
        <?php while ($row_post = $result_posts->fetch_assoc()): ?>
          <?php
          $sql_likes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_contenu = " . $row_post['id'];
          $likes_result = $conn->query($sql_likes);
          $likes_row = $likes_result->fetch_assoc();
          $like_count = $likes_row['like_count'];

          $sql_nbcom = "SELECT COUNT(*) AS com_count FROM commentaires WHERE id_contenu =" . $row_post['id'] . ";";
          $nbcom_result = $conn->query($sql_nbcom);
          $nbcom_row = $nbcom_result->fetch_assoc();
          $nbcom_count = $nbcom_row['com_count'];
          ?>

          <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200 transition-transform duration-300 hover:-translate-y-1 hover:shadow-md">
            <div class="p-3 flex items-center">
              <div class="flex items-center cursor-pointer" onclick="location.href='user.php?user_id=<?= $row_post['id_utilisateur'] ?>'">
                <div class="w-8 h-8 rounded-full overflow-hidden bg-blue-500 mr-3 flex-shrink-0 flex items-center justify-center text-white font-bold">
                  <?= strtoupper(substr($row_post['pseudo'], 0, 1)) ?>
                </div>
                <div>
                  <h3 class="font-semibold text-sm text-gray-800"><?= $row_post["pseudo"] ?></h3>
                </div>
              </div>
            </div>

            <div class="relative pt-[100%]">
              <img src="images/<?= $row_post["chemin_image"] ?>" alt="Image du post"
                class="absolute top-0 left-0 w-full h-full object-contain">
              <div class="absolute top-0 left-0 w-full h-full bg-black bg-opacity-40 opacity-0 hover:opacity-100 transition-opacity duration-300 flex items-center justify-center cursor-pointer"
                onclick="location.href='view_comments.php?post_id=<?= $row_post['id'] ?>'">
                <div class="flex gap-8 text-white font-semibold">
                  <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                      <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                    </svg>
                    <?= $like_count ?>
                  </div>
                  <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                      <path fill-rule="evenodd" d="M5.337 21.718a6.707 6.707 0 01-.533-.074.75.75 0 01-.44-1.223 3.73 3.73 0 00.814-1.686c.023-.115-.022-.317-.254-.543C3.274 16.587 2.25 14.41 2.25 12c0-5.03 4.428-9 9.75-9s9.75 3.97 9.75 9c0 5.03-4.428 9-9.75 9-.833 0-1.643-.097-2.417-.279a6.721 6.721 0 01-4.246.997z" clip-rule="evenodd" />
                    </svg>
                    <?= $nbcom_count ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-3">
              <div class="flex items-center justify-between mb-2">
                <form method="post" action="" class="inline">
                  <input type="hidden" name="post_id" value="<?= $row_post['id'] ?>">
                  <button type="submit" name="like" class="flex items-center focus:outline-none transition-colors duration-200">
                    <?php
                    $user_liked = false;
                    if ($user_id > 0) {
                      $user_like_check = "SELECT * FROM likes WHERE id_contenu = {$row_post['id']} AND id_utilisateur = $user_id";
                      $user_like_result = $conn->query($user_like_check);
                      $user_liked = ($user_like_result->num_rows > 0);
                    }
                    ?>
                    <?php if ($user_liked): ?>
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-red-500">
                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                      </svg>
                    <?php else: ?>
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 hover:text-red-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                      </svg>
                    <?php endif; ?>
                  </button>
                </form>

                <a href="view_comments.php?post_id=<?= $row_post['id'] ?>" class="flex items-center hover:text-blue-500 transition-colors duration-200">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" />
                  </svg>
                </a>
              </div>

              <p class="text-sm font-semibold text-gray-800"><?= $like_count ?> j'aime</p>

              <p class="text-sm mt-1 line-clamp-2 text-gray-700">
                <span class="font-semibold"><?= $row_post["pseudo"] ?></span>
                <?= htmlspecialchars(substr($row_post["description"], 0, 100)) . (strlen($row_post["description"]) > 100 ? '...' : '') ?>
              </p>

              <?php if ($nbcom_count > 0): ?>
                <a href="view_comments.php?post_id=<?= $row_post['id'] ?>" class="text-gray-500 text-xs mt-2 block hover:text-blue-500 transition-colors duration-200">
                  Voir les <?= $nbcom_count ?> commentaires
                </a>
              <?php endif; ?>

              <p class="text-gray-400 text-xs mt-1">
                <?= date('j M Y', strtotime($row_post["date_publication"])) ?>
              </p>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

      <div class="flex justify-center mt-8 mb-8">
        <div class="flex items-center gap-2 bg-white rounded-lg shadow-sm p-1">
          <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>" class="p-2 rounded-md hover:bg-gray-100 transition-colors duration-200 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
              </svg>
            </a>
          <?php else: ?>
            <span class="p-2 text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
              </svg>
            </span>
          <?php endif; ?>

          <span class="px-4 py-2 font-medium text-gray-700">
            <?= $currentPage ?> / <?= $totalPages ?>
          </span>

          <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>" class="p-2 rounded-md hover:bg-gray-100 transition-colors duration-200 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
              </svg>
            </a>
          <?php else: ?>
            <span class="p-2 text-gray-300">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
              </svg>
            </span>
          <?php endif; ?>
        </div>
      </div>

    <?php else: ?>
      <div class="flex flex-col items-center justify-center min-h-[60vh] text-center p-4">
        <div class="bg-white rounded-lg shadow-md p-8 max-w-md w-full">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-20 h-20 text-gray-300 mx-auto mb-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
          </svg>

          <h2 class="text-2xl font-bold text-gray-800 mb-2">Aucun post trouvé</h2>
          <p class="text-gray-600 mb-6">Commencez à suivre des utilisateurs ou créez votre premier post</p>

          <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] == true): ?>
            <a href="Creerpos.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-600 transition-colors duration-200">
              Créer un post
            </a>
          <?php else: ?>
            <a href="connexion.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-600 transition-colors duration-200">
              Se connecter
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
$conn->close();
?>