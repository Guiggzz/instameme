<?php
ob_start();

session_start();
require_once 'db.php';

if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] === true) {
    header("Location: index.php");
    exit();
}

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $pseudo = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, pseudo, mot_de_passe FROM utilisateurs WHERE pseudo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pseudo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (strlen($user['mot_de_passe']) == 32) {
            $password_match = (md5($password) === $user['mot_de_passe']);
        } else {
            $password_match = password_verify($password, $user['mot_de_passe']);
        }

        if ($password_match) {
            $_SESSION['connecte'] = true;
            $_SESSION['id_utilisateur'] = $user['id'];
            $_SESSION['pseudo'] = $user['pseudo'];

            header("Location: index.php");
            exit();
        } else {
            $login_error = "Mot de passe incorrect.";
        }
    } else {
        $login_error = "Nom d'utilisateur introuvable.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $pseudo = $_POST['reg_username'];
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];
    $dateinscription = date('Y-m-d H:i:s');

    if ($password !== $confirm_password) {
        $register_error = "Les mots de passe ne correspondent pas.";
    } else {
        $sql_check = "SELECT * FROM utilisateurs WHERE pseudo = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("s", $pseudo);
        $stmt->execute();
        $result_check = $stmt->get_result();

        if ($result_check->num_rows > 0) {
            $register_error = "Ce nom d'utilisateur existe déjà.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO utilisateurs (pseudo, mot_de_passe, date_inscription) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($sql_insert);
            $insert_stmt->bind_param("sss", $pseudo, $hashed_password, $dateinscription);

            if ($insert_stmt->execute()) {
                $new_user_id = $conn->insert_id;

                $_SESSION['connecte'] = true;
                $_SESSION['id_utilisateur'] = $new_user_id;
                $_SESSION['pseudo'] = $pseudo;

                header("Location: index.php");
                exit();
            } else {
                $register_error = "Erreur lors de la création du compte: " . $conn->error;
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | InstaMeme</title>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-md">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Connexion à InstaMeme</h2>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-6">
                    <?php if (!empty($login_error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                        <input type="password" id="password" name="password" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit" name="login"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Se connecter
                        </button>
                        <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="#register-form" id="show-register">
                            Créer un compte
                        </a>
                    </div>
                </form>

                <hr class="my-6 border-t border-gray-300">

                <div id="register-form" class="hidden">
                    <h3 class="text-xl font-bold text-center text-gray-800 mb-4">Créer un compte</h3>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <?php if (isset($register_error)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                <?php echo $register_error; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label for="reg_username" class="block text-gray-700 text-sm font-bold mb-2">Nom d'utilisateur</label>
                            <input type="text" id="reg_username" name="reg_username" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="mb-4">
                            <label for="reg_password" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                            <input type="password" id="reg_password" name="reg_password" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="mb-6">
                            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirmer le mot de passe</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit" name="register"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
                                Créer un compte
                            </button>
                            <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="#" id="show-login">
                                J'ai déjà un compte
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form');
            const registerForm = document.getElementById('register-form');
            const showRegisterLink = document.getElementById('show-register');
            const showLoginLink = document.getElementById('show-login');

            showRegisterLink.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
            });

            showLoginLink.addEventListener('click', function(e) {
                e.preventDefault();
                registerForm.classList.add('hidden');
                loginForm.classList.remove('hidden');
            });

            <?php if (isset($register_error)): ?>
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
            <?php endif; ?>
        });
    </script>
</body>

</html>