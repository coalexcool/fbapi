<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';
$name = $_POST['name'] ?? '';
$name_code = $_POST['name_code'] ?? '';
$create_propose = $_POST['create_propose'] ?? 0;
$answer_propose = $_POST['answer_propose'] ?? 0;
$sell_player_to_market = $_POST['sell_player_to_market'] ?? 0;

// Verifica che username, token, name e name_code siano forniti
if (!$username || !$token || !$name || !$name_code) {
    echo json_encode(['success' => false, 'error' => 'I campi username, token, name e name_code sono obbligatori']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Verifica le credenziali dell'utente (username e token) e controlla se è superadmin
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND token = ?');
    $stmt->execute([$username, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se l'utente non esiste o il token è errato
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Credenziali non valide']);
        exit;
    }

    // Verifica che l'utente sia superadmin
    if ($user['superadmin'] != 1) {
        echo json_encode(['success' => false, 'error' => 'Non hai i permessi necessari per aggiungere un permesso']);
        exit;
    }

    // Query per inserire un nuovo permesso nella tabella permissions
    $stmt = $pdo->prepare('
        INSERT INTO permissions (name, name_code, create_propose, answer_propose, sell_player_to_market)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$name, $name_code, $create_propose, $answer_propose, $sell_player_to_market]);

    // Restituisce una risposta di successo
    echo json_encode(['success' => true, 'message' => 'Permesso aggiunto correttamente']);

} catch (PDOException $e) {
    // In caso di errore restituisce un messaggio d'errore
    echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiunta del permesso: ' . $e->getMessage()]);
}
?>
