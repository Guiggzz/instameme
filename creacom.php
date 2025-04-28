<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

$error_messages = [];

if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    $_SESSION['redirect_after_login'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

    header("Location: connexion.php?message=login_required");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';
    $user_id = $_SESSION['id_utilisateur'];

    if ($post_id <= 0) {
        $error_messages[] = "ID de post invalide.";
    }

    if (empty($commentaire)) {
        $error_messages[] = "Le commentaire ne peut pas être vide.";
    }

    if (strlen($commentaire) > 255) {
        $error_messages[] = "Le commentaire est trop long (maximum 255 caractères).";
    }

    if (empty($error_messages)) {
        $check_post = "SELECT id FROM contenus WHERE id = ?";
        $stmt_check = $conn->prepare($check_post);
        $stmt_check->bind_param("i", $post_id);
        $stmt_check->execute();
        $post_result = $stmt_check->get_result();

        if ($post_result->num_rows === 0) {
            $error_messages[] = "Le post spécifié n'existe pas.";
        } else {
            $sql = "INSERT INTO commentaires (id_contenu, id_utilisateur, message, date_publication) 
                    VALUES (?, ?, ?, NOW())";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $post_id, $user_id, $commentaire);

            if ($stmt->execute()) {
                $redirect_url = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "view_comments.php?post_id=$post_id";

                if (!empty($error_messages)) {
                    $_SESSION['comment_errors'] = $error_messages;
                }

                header("Location: $redirect_url");
                exit;
            } else {
                $error_messages[] = "Erreur lors de l'ajout du commentaire: " . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

if (!empty($error_messages)) {
    $_SESSION['comment_errors'] = $error_messages;
    $redirect_url = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "index.php";
    header("Location: $redirect_url");
    exit;
}

header("Location: index.php");
exit;
