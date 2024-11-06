<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$id_trigger = $_POST['id_trigger'] ?? null;
$from_team = $_POST['from_team'] ?? null;
$status = $_POST['status'] ?? 0; // Stato di default "0" (non elaborato)
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';

// Verifica che tutti i campi obbligatori siano stati forniti
if (!$id_trigger || !$from_team || !$username || !$token) {
    echo json_encode(['error' => 'ID del trigger, team, username e token sono obbligatori']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Verifica l'utente tramite username e token
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND token = ?');
    $stmt->execute([$username, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se l'utente esiste
    if (!$user) {
        echo json_encode(['error' => 'Accesso negato. Credenziali non valide']);
        exit;
    }

    // Inserisci la richiesta di trigger nella tabella triggers_requests
    $stmt = $pdo->prepare('INSERT INTO triggers_requests (id_trigger, from_team, status) VALUES (?, ?, ?)');
    $stmt->execute([$id_trigger, $from_team, $status]);

    echo json_encode(['success' => 'Richiesta di trigger inviata correttamente', 'id_trigger_request' => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante l\'invio della richiesta di trigger: ' . $e->getMessage()]);
}
?>
