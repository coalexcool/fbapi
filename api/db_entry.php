<?php
include $_SERVER['DOCUMENT_ROOT']."/config/config_db.php";

// Funzione per ottenere la connessione al database usando PDO
function getDatabaseConnection() {
    global $host, $db, $port, $user, $pass;

    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";
    
    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo 'Errore di connessione: ' . $e->getMessage();
        exit;
    }
}

?>
