<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$id_trigger = $_POST['id_trigger'] ?? null;
$name = $_POST['name'] ?? '';
$name_code = $_POST['name_code'] ?? '';
$points = $_POST['points'] ?? null;
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';

// Verifica che tutti i campi obbligatori siano stati forniti
if (!$id_trigger || !$name || !$name_code || !$points || !$username || !$token) {
    echo json_encode(['error' => 'ID del trigger, nome, name_code, punti, username e token sono obbligatori']);
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
        echo json_encode(['error' => 'Accesso negato. Solo i superadmin possono aggiornare trigger']);
        exit;
    }

    // Aggiorna i dati del trigger nel database
    $stmt = $pdo->prepare('UPDATE triggers SET name = ?, name_code = ?, points = ? WHERE id_trigger = ?');
    $stmt->execute([$name, $name_code, $points, $id_trigger]);

    // Verifica se il trigger è stato aggiornato
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => 'Trigger aggiornato correttamente']);
    } else {
        echo json_encode(['error' => 'Il trigger non esiste o i dati non sono stati modificati']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante l\'aggiornamento del trigger: ' . $e->getMessage()]);
}
?>
