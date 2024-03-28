<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "instameme";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}
