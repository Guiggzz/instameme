<?php
// Vérifier si une session n'est pas déjà démarrée avant d'en démarrer une nouvelle
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instameme</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="img/Instameme - Logo.png" type="image/x-icon">
</head>

<body class="bg-gray-50">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="index.php" class="flex items-center">
                    <img src="img/icon.png" alt="Logo" class="h-10 w-auto">
                    <span class="ml-2 text-xl font-bold text-gray-800 hidden sm:block">Instameme</span>
                </a>

                <form action="search.php" method="POST" class="hidden md:flex items-center max-w-xs w-full mx-4">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 start-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <input name="recherche" type="text" placeholder="Rechercher" required
                            class="bg-gray-100 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2.5 
                                     border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </form>

                <nav class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 hover:text-blue-500" title="Accueil">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </a>

                    <?php if (isset($_SESSION["connecte"]) && $_SESSION["connecte"] == true): ?>
                        <a href="Creerpos.php" class="text-gray-700 hover:text-blue-500" title="Créer un post">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </a>

                        <a href="user.php?user_id=<?= $_SESSION['id_utilisateur'] ?>" class="text-gray-700 hover:text-blue-500" title="Profil">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>

                        <!-- Menu déroulant utilisateur -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-1 focus:outline-none">
                                <span class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                    <?= isset($_SESSION['pseudo']) ? strtoupper(substr($_SESSION['pseudo'], 0, 1)) : 'U' ?>
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 hidden sm:block">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
                                style="display: none;">
                                <?php if (isset($_SESSION['pseudo'])): ?>
                                    <div class="px-4 py-2 text-sm text-gray-700 border-b border-gray-100">
                                        Bonjour, <?= $_SESSION['pseudo'] ?>
                                    </div>
                                <?php endif; ?>

                                <a href="user.php?user_id=<?= $_SESSION['id_utilisateur'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Profil
                                </a>

                                <div class="block md:hidden px-4 py-2">
                                    <form action="search.php" method="POST" class="flex items-center">
                                        <input name="recherche" type="text" placeholder="Rechercher" required
                                            class="bg-gray-100 text-sm rounded p-2 w-full border border-gray-200">
                                    </form>
                                </div>

                                <a href="deco.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="connexion.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150 ease-in-out">
                            Connexion
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <div class="md:hidden bg-white border-b border-gray-200 px-4 py-2">
        <form action="search.php" method="POST" class="flex items-center">
            <div class="relative w-full">
                <div class="absolute inset-y-0 start-0 flex items-center pl-3 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <input name="recherche" type="text" placeholder="Rechercher" required
                    class="bg-gray-100 text-gray-900 text-sm rounded-lg block w-full pl-10 p-2 
                             border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>