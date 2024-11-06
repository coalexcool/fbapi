<?php
// Inclusione del file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Funzione per generare un nuovo token
function generateNewToken($length = 50) {
    return bin2hex(random_bytes($length / 2));
}

// Gestione della conferma
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if (empty($token)) {
        echo "Errore: il token non è valido o non è stato fornito.";
        exit;
    }

    try {
        // Connessione al database
        $pdo = getDatabaseConnection();

        // Verifica del token
        $stmt = $pdo->prepare("SELECT * FROM users WHERE token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Aggiorna lo status dell'utente e rimuovi il token di conferma (impostandolo a stringa vuota)
            $updateStmt = $pdo->prepare("UPDATE users SET token = '', status = 1 WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            // Messaggio di successo
            echo "Registrazione confermata! Il tuo account è ora attivo.";
        } else {
            echo "Errore: token non valido o utente non trovato.";
        }
    } catch (PDOException $e) {
        echo "Errore: " . $e->getMessage();
    }
} else {
    echo "Errore: Token non fornito nell'URL.";
}
?>
