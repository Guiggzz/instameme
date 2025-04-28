<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["connecte"]) || $_SESSION["connecte"] !== true) {
    header("Location: connexion.php?message=login_required");
    exit;
}

require_once 'db.php';

$error_messages = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (empty($description)) {
        $error_messages[] = "La description ne peut pas être vide.";
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error_messages[] = "Veuillez sélectionner une image valide.";
    } else {
        $image = $_FILES['image'];
        $userId = $_SESSION['id_utilisateur'];
        $imageName = time() . '_' . basename($image['name']); // Ajouter un timestamp pour éviter les doublons
        $imageDirectory = 'images/';
        $imagePath = $imageDirectory . $imageName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($image['type'], $allowedTypes)) {
            $error_messages[] = "Type de fichier non autorisé. Veuillez télécharger une image JPEG, PNG, GIF ou WEBP.";
        }

        if ($image['size'] > 5 * 1024 * 1024) {
            $error_messages[] = "L'image est trop volumineuse. Taille maximale: 5 MB.";
        }

        if (empty($error_messages)) {
            if (!is_dir($imageDirectory)) {
                mkdir($imageDirectory, 0755, true);
            }

            if (move_uploaded_file($image['tmp_name'], $imagePath)) {
                $stmt = $conn->prepare("INSERT INTO contenus (id_utilisateur, description, chemin_image, date_publication) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iss", $userId, $description, $imageName);

                if ($stmt->execute()) {
                    $success_message = 'Post créé avec succès!';
                    header("Refresh: 2; URL=index.php");
                } else {
                    $error_messages[] = "Erreur lors de la création du post: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error_messages[] = "Erreur lors du téléchargement de l'image.";
            }
        }
    }
}

require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Post | InstaMeme</title>
</head>

<body class="bg-gray-50 min-h-screen pb-20">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Créer un nouveau post</h1>

                <?php if (!empty($error_messages)): ?>
                    <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                        <p class="font-bold mb-1">Des erreurs sont survenues:</p>
                        <ul class="list-disc ml-5">
                            <?php foreach ($error_messages as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                        <?php echo htmlspecialchars($success_message); ?>
                        <p class="mt-1">Redirection en cours...</p>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" required
                            class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            rows="4" placeholder="Décrivez votre post..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    </div>

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Image</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer hover:bg-gray-50 transition-colors" id="dropzone">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="flex justify-center text-sm text-gray-600">
                                    <label for="image" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Télécharger une image</span>
                                        <input id="image" name="image" type="file" class="sr-only" accept="image/*" required>
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF ou WEBP jusqu'à 5MB</p>
                                <p class="text-sm text-gray-500 mt-2" id="file-name">Aucun fichier sélectionné</p>
                                <div class="hidden mt-2" id="image-preview-container">
                                    <img id="image-preview" class="mx-auto max-h-40 object-contain" src="#" alt="Aperçu de l'image">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="hidden" id="preview-section">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aperçu du post</h3>
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="p-3 flex items-center border-b">
                                <div class="w-8 h-8 rounded-full overflow-hidden bg-blue-500 mr-3 flex items-center justify-center text-white font-bold">
                                    <?php echo isset($_SESSION['pseudo']) ? strtoupper(substr($_SESSION['pseudo'], 0, 1)) : 'U'; ?>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-sm text-gray-800"><?php echo isset($_SESSION['pseudo']) ? $_SESSION['pseudo'] : 'Utilisateur'; ?></h3>
                                </div>
                            </div>

                            <div class="relative pt-[100%]">
                                <img id="preview-image" src="#" alt="Aperçu"
                                    class="absolute top-0 left-0 w-full h-full object-contain bg-black">
                            </div>

                            <div class="p-3">
                                <p class="text-sm mt-1 text-gray-700">
                                    <span class="font-semibold"><?php echo isset($_SESSION['pseudo']) ? $_SESSION['pseudo'] : 'Utilisateur'; ?></span>
                                    <span id="preview-description"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-4">
                        <a href="index.php" class="px-6 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-75 transition-colors">
                            Publier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('image');
            const dropzone = document.getElementById('dropzone');
            const fileName = document.getElementById('file-name');
            const previewContainer = document.getElementById('image-preview-container');
            const imagePreview = document.getElementById('image-preview');
            const previewSection = document.getElementById('preview-section');
            const previewImage = document.getElementById('preview-image');
            const previewDescription = document.getElementById('preview-description');
            const descriptionInput = document.getElementById('description');

            descriptionInput.addEventListener('input', function() {
                previewDescription.textContent = this.value;
            });

            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    fileName.textContent = file.name;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        previewImage.src = e.target.result;
                        previewContainer.classList.remove('hidden');
                        previewSection.classList.remove('hidden');
                    }
                    reader.readAsDataURL(file);
                } else {
                    fileName.textContent = 'Aucun fichier sélectionné';
                    previewContainer.classList.add('hidden');
                    previewSection.classList.add('hidden');
                }
            });

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropzone.classList.add('border-blue-300', 'bg-blue-50');
            }

            function unhighlight() {
                dropzone.classList.remove('border-blue-300', 'bg-blue-50');
            }

            dropzone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                if (files && files.length) {
                    fileInput.files = files;
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            }

            dropzone.addEventListener('click', function() {
                fileInput.click();
            });
        });
    </script>
</body>

</html>