<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    $_SESSION['redirect_after_login'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

    header("Location: connexion.php?message=login_required");
    exit;
}

if (isset($_POST['like']) && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];
    $user_id = $_SESSION['id_utilisateur'];

    if ($post_id <= 0) {
        $_SESSION['error_message'] = "ID de post invalide";
        header("Location: index.php");
        exit;
    }

    $check_post = "SELECT id FROM contenus WHERE id = ?";
    $stmt_check = $conn->prepare($check_post);
    $stmt_check->bind_param("i", $post_id);
    $stmt_check->execute();
    $post_result = $stmt_check->get_result();

    if ($post_result->num_rows === 0) {
        $_SESSION['error_message'] = "Le post spécifié n'existe pas";
        header("Location: index.php");
        exit;
    }
    $stmt_check->close();

    $check_like = "SELECT * FROM likes WHERE id_contenu = ? AND id_utilisateur = ?";
    $stmt_like = $conn->prepare($check_like);
    $stmt_like->bind_param("ii", $post_id, $user_id);
    $stmt_like->execute();
    $like_result = $stmt_like->get_result();

    if ($like_result->num_rows > 0) {
        $remove_like = "DELETE FROM likes WHERE id_contenu = ? AND id_utilisateur = ?";
        $stmt_remove = $conn->prepare($remove_like);
        $stmt_remove->bind_param("ii", $post_id, $user_id);
        $stmt_remove->execute();
        $stmt_remove->close();
    } else {
        $add_like = "INSERT INTO likes (id_contenu, id_utilisateur) VALUES (?, ?)";
        $stmt_add = $conn->prepare($add_like);
        $stmt_add->bind_param("ii", $post_id, $user_id);
        $stmt_add->execute();
        $stmt_add->close();
    }
    $stmt_like->close();

    $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php');
    header("Location: " . $redirect_url);
    exit;
} else {
    header("Location: index.php");
    exit;
}
