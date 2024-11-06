<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$name = $_POST['name'] ?? '';
$name_code = $_POST['name_code'] ?? '';
$points = $_POST['points'] ?? null;
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';

// Verifica che tutti i campi obbligatori siano stati forniti
if (!$name || !$name_code || !$points || !$username || !$token) {
    echo json_encode(['error' => 'Tutti i campi obbligatori devono essere forniti']);
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
        echo json_encode(['error' => 'Accesso negato. Solo i superadmin possono aggiungere trigger']);
        exit;
    }

    // Inserisci il nuovo trigger nel database
    $stmt = $pdo->prepare('INSERT INTO triggers (name, name_code, points) VALUES (?, ?, ?)');
    $stmt->execute([$name, $name_code, $points]);

    echo json_encode(['success' => 'Trigger aggiunto correttamente', 'id_trigger' => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante l\'aggiunta del trigger: ' . $e->getMessage()]);
}
?>
