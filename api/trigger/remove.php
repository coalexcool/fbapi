<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$id_trigger = $_POST['id_trigger'] ?? null;
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';

// Verifica che tutti i campi obbligatori siano stati forniti
if (!$id_trigger || !$username || !$token) {
    echo json_encode(['error' => 'ID del trigger, username e token sono obbligatori']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Verifica l'utente tramite username e token
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND token = ?');
    $stmt->execute([$username, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se l'utente esiste e se è superadmin
    if (!$user || $user['superadmin'] != 1) {
        echo json_encode(['error' => 'Accesso negato. Solo i superadmin possono eliminare trigger']);
        exit;
    }

    // Elimina il trigger dal database
    $stmt = $pdo->prepare('DELETE FROM triggers WHERE id_trigger = ?');
    $stmt->execute([$id_trigger]);

    // Verifica se il trigger è stato eliminato
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Trigger eliminato correttamente']);
    } else {
        echo json_encode(['error' => 'Il trigger non esiste']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante l\'eliminazione del trigger: ' . $e->getMessage()]);
}
?>
