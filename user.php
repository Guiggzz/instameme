<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

$user_id = isset($_SESSION['id_utilisateur']) ? $_SESSION['id_utilisateur'] : 0;
$visiting_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil utilisateur | InstaMeme</title>
</head>

<body class="bg-gray-50 min-h-screen pb-10">
    <div class="container mx-auto px-4 py-6 max-w-7xl">
        <?php if (isset($_GET['user_id']) && !empty($_GET['user_id'])): ?>
            <?php
            $visiting_user_id = (int)$_GET['user_id'];

            $sql_user = "SELECT id, pseudo, date_inscription FROM utilisateurs WHERE id = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("i", $visiting_user_id);
            $stmt_user->execute();
            $user_result = $stmt_user->get_result();

            if ($user_result->num_rows === 1) {
                $user_info = $user_result->fetch_assoc();

                $sql_post_count = "SELECT COUNT(*) as post_count FROM contenus WHERE id_utilisateur = ?";
                $stmt_count = $conn->prepare($sql_post_count);
                $stmt_count->bind_param("i", $visiting_user_id);
                $stmt_count->execute();
                $count_result = $stmt_count->get_result();
                $post_count = $count_result->fetch_assoc()['post_count'];

                $sql_likes_count = "SELECT COUNT(*) as likes_count FROM likes 
                                    INNER JOIN contenus ON likes.id_contenu = contenus.id 
                                    WHERE contenus.id_utilisateur = ?";
                $stmt_likes = $conn->prepare($sql_likes_count);
                $stmt_likes->bind_param("i", $visiting_user_id);
                $stmt_likes->execute();
                $likes_result = $stmt_likes->get_result();
                $likes_count = $likes_result->fetch_assoc()['likes_count'];
            ?>

                <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                    <div class="p-6 md:p-8">
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                            <div class="w-20 h-20 md:w-28 md:h-28 rounded-full overflow-hidden bg-blue-500 flex-shrink-0 flex items-center justify-center text-white text-3xl md:text-4xl font-bold">
                                <?= strtoupper(substr($user_info['pseudo'], 0, 1)) ?>
                            </div>

                            <div class="flex-grow text-center md:text-left">
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($user_info['pseudo']) ?></h1>
                                <p class="text-gray-500 text-sm mb-4">Membre depuis <?= date('F Y', strtotime($user_info['date_inscription'])) ?></p>

                                <div class="flex justify-center md:justify-start space-x-6 text-center">
                                    <div>
                                        <span class="font-bold text-xl block"><?= $post_count ?></span>
                                        <span class="text-gray-500 text-sm">Publications</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-xl block"><?= $likes_count ?></span>
                                        <span class="text-gray-500 text-sm">J'aime reçus</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
                $sql_posts = "SELECT contenus.id, contenus.id_utilisateur, contenus.description, contenus.chemin_image, 
                              contenus.date_publication, utilisateurs.pseudo
                              FROM contenus 
                              INNER JOIN utilisateurs ON contenus.id_utilisateur = utilisateurs.id 
                              WHERE contenus.id_utilisateur = ?
                              ORDER BY contenus.date_publication DESC";

                $stmt_posts = $conn->prepare($sql_posts);
                $stmt_posts->bind_param("i", $visiting_user_id);
                $stmt_posts->execute();
                $result_posts = $stmt_posts->get_result();

                if ($result_posts->num_rows > 0):
                ?>

                    <h2 class="text-xl font-bold text-gray-800 mb-4 px-2">Publications</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
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
                                    <div class="flex items-center cursor-pointer">
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
                                        <form method="post" action="like_action.php" class="inline">
                                            <input type="hidden" name="post_id" value="<?= $row_post['id'] ?>">
                                            <input type="hidden" name="redirect_url" value="user.php?user_id=<?= $visiting_user_id ?>">
                                            <button type="submit" name="like_action" class="flex items-center focus:outline-none transition-colors duration-200">
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

                <?php else: ?>
                    <div class="flex flex-col items-center justify-center bg-white rounded-lg shadow-sm p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-300 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Aucune publication</h2>
                        <p class="text-gray-600 mb-4">Cet utilisateur n'a pas encore partagé de contenu.</p>
                    </div>
                <?php endif; ?>

            <?php } else { ?>
                <div class="flex flex-col items-center justify-center bg-white rounded-lg shadow-md p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-300 mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Utilisateur non trouvé</h2>
                    <p class="text-gray-600 mb-4">Cet utilisateur n'existe pas ou a été supprimé.</p>
                    <a href="index.php" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                        Retour à l'accueil
                    </a>
                </div>
            <?php } ?>

        <?php else: ?>
            <div class="flex flex-col items-center justify-center min-h-[50vh] bg-white rounded-lg shadow-md p-8 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-300 mb-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Paramètre manquant</h2>
                <p class="text-gray-600 mb-4">Aucun identifiant d'utilisateur spécifié.</p>
                <a href="index.php" class="inline-block bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                    Retour à l'accueil
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>