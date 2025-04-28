<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du post | InstaMeme</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen">
    <?php
    require_once 'header.php';
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

            $sql_likes = "SELECT COUNT(*) AS like_count FROM likes WHERE id_contenu = $post_id";
            $likes_result = $conn->query($sql_likes);
            $likes_row = $likes_result->fetch_assoc();
            $like_count = $likes_row['like_count'];

            $user_liked = false;
            if (isset($_SESSION['id_utilisateur'])) {
                $user_id = $_SESSION['id_utilisateur'];
                $user_like_check = "SELECT * FROM likes WHERE id_contenu = $post_id AND id_utilisateur = $user_id";
                $user_like_result = $conn->query($user_like_check);
                $user_liked = ($user_like_result->num_rows > 0);
            }

            $sql_comments = "SELECT commentaires.message, commentaires.date_publication, utilisateurs.pseudo AS user_pseudo, utilisateurs.id AS user_id 
                         FROM commentaires 
                         INNER JOIN utilisateurs ON commentaires.id_utilisateur = utilisateurs.id 
                         WHERE commentaires.id_contenu = $post_id
                         ORDER BY commentaires.date_publication DESC";
            $result_comments = $conn->query($sql_comments);
    ?>

            <div class="container mx-auto max-w-6xl px-4 py-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="md:flex">
                        <div class="md:w-3/5 bg-black flex items-center justify-center">
                            <img src="images/<?= $row_post["chemin_image"] ?>" alt="Image du post" class="max-h-[600px] w-full object-contain">
                        </div>

                        <div class="md:w-2/5 flex flex-col">
                            <div class="p-4 border-b flex items-center">
                                <div class="w-10 h-10 rounded-full overflow-hidden bg-blue-500 mr-3 flex items-center justify-center text-white font-bold">
                                    <?= strtoupper(substr($row_post["user_pseudo"], 0, 1)) ?>
                                </div>
                                <a href="user.php?user_id=<?= $row_post['id_utilisateur'] ?>" class="font-semibold text-gray-800 hover:underline">
                                    <?= $row_post["user_pseudo"] ?>
                                </a>
                            </div>

                            <div class="flex-grow overflow-y-auto h-64 md:h-auto">
                                <div class="p-4 border-b">
                                    <div class="flex items-start mb-3">
                                        <div class="w-8 h-8 rounded-full overflow-hidden bg-blue-500 mr-3 flex items-center justify-center text-white font-bold text-xs">
                                            <?= strtoupper(substr($row_post["user_pseudo"], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <a href="user.php?user_id=<?= $row_post['id_utilisateur'] ?>" class="font-semibold text-gray-800 hover:underline mr-2">
                                                <?= $row_post["user_pseudo"] ?>
                                            </a>
                                            <p class="text-gray-700"><?= nl2br(htmlspecialchars($row_post["description"])) ?></p>
                                            <p class="text-xs text-gray-500 mt-1"><?= date('j M Y', strtotime($row_post["date_publication"])) ?></p>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($result_comments->num_rows > 0): ?>
                                    <div class="px-4 py-2">
                                        <?php while ($row_comment = $result_comments->fetch_assoc()): ?>
                                            <div class="py-3 flex">
                                                <div class="w-8 h-8 rounded-full overflow-hidden bg-blue-500 mr-3 flex items-center justify-center text-white font-bold text-xs">
                                                    <?= strtoupper(substr($row_comment['user_pseudo'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <a href="user.php?user_id=<?= $row_comment['user_id'] ?>" class="font-semibold text-gray-800 hover:underline mr-2">
                                                        <?= $row_comment['user_pseudo'] ?>
                                                    </a>
                                                    <span class="text-gray-700"><?= nl2br(htmlspecialchars($row_comment['message'])) ?></span>
                                                    <p class="text-xs text-gray-500 mt-1"><?= date('j M Y', strtotime($row_comment['date_publication'] ?? 'now')) ?></p>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="p-4 text-center text-gray-500">
                                        Aucun commentaire pour ce post.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-4 border-t">
                                <div class="flex items-center justify-between mb-3">
                                    <form method="post" action="like.php" class="inline">
                                        <input type="hidden" name="post_id" value="<?= $post_id ?>">
                                        <input type="hidden" name="redirect_url" value="view_comments.php?post_id=<?= $post_id ?>">
                                        <button type="submit" name="like" class="focus:outline-none transition-colors duration-200">
                                            <?php if ($user_liked): ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-red-500">
                                                    <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                                </svg>
                                            <?php else: ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7 hover:text-red-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </div>

                                <p class="font-semibold text-sm mb-1"><?= $like_count ?> j'aime</p>
                                <p class="text-xs text-gray-500 mb-4"><?= date('j M Y', strtotime($row_post["date_publication"])) ?></p>

                                <form method="post" action="creacom.php" class="mt-3">
                                    <input type="hidden" name="post_id" value="<?= $post_id ?>">
                                    <div class="flex">
                                        <textarea name="commentaire" rows="1"
                                            class="flex-grow px-3 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-300 resize-none"
                                            placeholder="Ajouter un commentaire..."></textarea>
                                        <button type="submit"
                                            class="bg-blue-500 text-white px-4 rounded-r-lg hover:bg-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                            Publier
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="index.php" class="inline-flex items-center text-blue-500 hover:text-blue-700 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        Retour à l'accueil
                    </a>
                </div>
            </div>

        <?php } else { ?>
            <div class="container mx-auto px-4 py-16 text-center">
                <div class="bg-white rounded-lg shadow-md p-8 max-w-md mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-20 h-20 text-gray-300 mx-auto mb-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Aucun post trouvé</h2>
                    <p class="text-gray-600 mb-6">Le post que vous recherchez n'existe pas ou a été supprimé.</p>
                    <a href="index.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-600 transition-colors duration-200">
                        Retour à l'accueil
                    </a>
                </div>
            </div>
        <?php }
    } else { ?>
        <div class="container mx-auto px-4 py-16 text-center">
            <div class="bg-white rounded-lg shadow-md p-8 max-w-md mx-auto">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-20 h-20 text-gray-300 mx-auto mb-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Paramètre manquant</h2>
                <p class="text-gray-600 mb-6">Aucun identifiant de post n'a été spécifié.</p>
                <a href="index.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg font-medium shadow-sm hover:bg-blue-600 transition-colors duration-200">
                    Retour à l'accueil
                </a>
            </div>
        </div>
    <?php } ?>
</body>

</html>