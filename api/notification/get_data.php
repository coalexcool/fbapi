<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recupera i dati inviati tramite POST
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';

// Verifica che i campi obbligatori siano stati forniti
if (!$username || !$token) {
    echo json_encode(['success' => false, 'error' => 'Username e token sono obbligatori']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Verifica le credenziali dell'utente
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND token = ?');
    $stmt->execute([$username, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se l'utente non esiste o il token è errato
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Credenziali non valide']);
        exit;
    }

    // Query per ottenere tutte le notifiche dalla tabella notifications
    $stmt = $pdo->prepare('
        SELECT n.id_notification, n.user_from_id, n.user_middle_id, n.user_to_id, n.proposal_json, n.created_at, n.status,
               u_from.username AS from_username, u_to.username AS to_username
        FROM notifications n
        JOIN users u_from ON n.user_from_id = u_from.id
        JOIN users u_to ON n.user_to_id = u_to.id
    ');
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Restituisce i dati in formato JSON
    echo json_encode(['success' => true, 'data' => $notifications]);

} catch (PDOException $e) {
    // In caso di errore restituisce un messaggio d'errore
    echo json_encode(['success' => false, 'error' => 'Errore durante il recupero dei dati: ' . $e->getMessage()]);
}
?>
