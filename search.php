<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_id = isset($_SESSION['id_utilisateur']) ? $_SESSION['id_utilisateur'] : 0;

require_once 'header.php';
require_once 'db.php';

$has_search = isset($_POST['recherche']) && !empty($_POST['recherche']);
$search_term = $has_search ? $conn->real_escape_string($_POST['recherche']) : '';

if (empty($search_term) && !isset($_SERVER['HTTP_REFERER'])) {
    $error_message = "Veuillez entrer un terme de recherche.";
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche | InstaMeme</title>
</head>

<body class="bg-gray-50 min-h-screen pb-10">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Résultats de recherche</h1>
            <?php if ($has_search): ?>
                <p class="text-gray-600">Résultats pour "<?= htmlspecialchars($search_term) ?>"</p>
            <?php endif; ?>

            <div class="md:hidden mt-4">
                <form action="search.php" method="POST" class="flex items-center w-full">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 start-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input name="recherche" type="text" value="<?= htmlspecialchars($search_term) ?>" placeholder="Rechercher" required
                            class="bg-gray-100 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2.5 
                                     border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" class="ml-2 px-4 py-2.5 bg-blue-500 text-white font-medium rounded-lg hover:bg-blue-600 transition-colors">
                        Rechercher
                    </button>
                </form>
            </div>
        </div>

        <?php if ($has_search): ?>
            <?php
            $sql_users = "SELECT id, pseudo, date_inscription FROM utilisateurs WHERE pseudo LIKE ? LIMIT 5";
            $stmt_users = $conn->prepare($sql_users);
            $search_param = "%" . $search_term . "%";
            $stmt_users->bind_param("s", $search_param);
            $stmt_users->execute();
            $result_users = $stmt_users->get_result();

            $sql_posts = "SELECT contenus.id, contenus.id_utilisateur, contenus.description, contenus.chemin_image, 
                          contenus.date_publication, utilisateurs.pseudo
                          FROM contenus 
                          INNER JOIN utilisateurs ON contenus.id_utilisateur = utilisateurs.id 
                          WHERE utilisateurs.pseudo LIKE ? OR contenus.description LIKE ?
                          ORDER BY contenus.date_publication DESC";
            $stmt_posts = $conn->prepare($sql_posts);
            $stmt_posts->bind_param("ss", $search_param, $search_param);
            $stmt_posts->execute();
            $result_posts = $stmt_posts->get_result();
            ?>

            <?php if ($result_users->num_rows > 0): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Utilisateurs</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php while ($user = $result_users->fetch_assoc()): ?>
                            <a href="user.php?user_id=<?= $user['id'] ?>" class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200 hover:shadow-md transition-shadow p-4 flex items-center">
                                <div class="w-12 h-12 rounded-full overflow-hidden bg-blue-500 mr-4 flex-shrink-0 flex items-center justify-center text-white font-bold text-xl">
                                    <?= strtoupper(substr($user['pseudo'], 0, 1)) ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($user['pseudo']) ?></h3>
                                    <p class="text-gray-500 text-sm">Membre depuis <?= date('M Y', strtotime($user['date_inscription'])) ?></p>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($result_posts->num_rows > 0): ?>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Publications</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    <?php while ($row_post = $result_posts->fetch_assoc()): ?>
                        <?php
                        $sql_likes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_contenu = ?";
                        $stmt_likes = $conn->prepare($sql_likes);
                        $stmt_likes->bind_param("i", $row_post['id']);
                        $stmt_likes->execute();
                        $likes_result = $stmt_likes->get_result();
                        $likes_row = $likes_result->fetch_assoc();
                        $like_count = $likes_row['like_count'];

                        $sql_nbcom = "SELECT COUNT(*) AS com_count FROM commentaires WHERE id_contenu = ?";
                        $stmt_comments = $conn->prepare($sql_nbcom);
                        $stmt_comments->bind_param("i", $row_post['id']);
                        $stmt_comments->execute();
                        $nbcom_result = $stmt_comments->get_result();
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
                                        <h3 class="font-semibold text-sm text-gray-800"><?= htmlspecialchars($row_post["pseudo"]) ?></h3>
                                    </div>
                                </div>
                            </div>

                            <div class="relative bg-black pt-[100%]">
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
                                    <form method="post" action="like_action.php" class="inline">
                                        <input type="hidden" name="post_id" value="<?= $row_post['id'] ?>">
                                        <input type="hidden" name="redirect_url" value="search.php">
                                        <button type="submit" name="like_action" class="flex items-center focus:outline-none transition-colors duration-200">
                                            <?php
                                            $user_liked = false;
                                            if ($user_id > 0) {
                                                $user_like_check = "SELECT * FROM likes WHERE id_contenu = ? AND id_utilisateur = ?";
                                                $stmt_check = $conn->prepare($user_like_check);
                                                $stmt_check->bind_param("ii", $row_post['id'], $user_id);
                                                $stmt_check->execute();
                                                $user_like_result = $stmt_check->get_result();
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
                                    <span class="font-semibold"><?= htmlspecialchars($row_post["pseudo"]) ?></span>
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
            <?php elseif ($result_users->num_rows == 0): ?>
                <div class="flex flex-col items-center justify-center min-h-[40vh] text-center p-4">
                    <div class="bg-white rounded-lg shadow-md p-8 max-w-md w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-300 mx-auto mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <h2 class="text-xl font-bold text-gray-800 mb-2">Aucun résultat trouvé</h2>
                        <p class="text-gray-600 mb-6">Aucun utilisateur ou post ne correspond à votre recherche "<?= htmlspecialchars($search_term) ?>".</p>
                        <a href="index.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-600 transition-colors duration-200">
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="flex flex-col items-center justify-center min-h-[50vh] text-center p-4">
                <div class="bg-white rounded-lg shadow-md p-8 max-w-md w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-300 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Rechercher</h2>
                    <p class="text-gray-600 mb-6">Utilisez la barre de recherche pour trouver des utilisateurs ou des publications.</p>

                    <form action="search.php" method="POST" class="flex flex-col space-y-4">
                        <input name="recherche" type="text" placeholder="Rechercher un utilisateur ou un post" required
                            class="w-full px-4 py-3 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-600 transition-colors duration-200">
                            Rechercher
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>