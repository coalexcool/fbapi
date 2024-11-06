<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';
$notification_id = $_POST['notification_id'] ?? '';
$response = $_POST['response'] ?? '';  // 'accepted' o 'rejected'

// Verifica che tutti i campi obbligatori siano stati forniti
if (!$username || !$token || !$notification_id || !$response) {
    echo json_encode(['error' => 'Tutti i campi obbligatori devono essere forniti']);
    exit;
}

// Verifica che la risposta sia valida
if ($response !== 'accepted' && $response !== 'rejected') {
    echo json_encode(['error' => 'Risposta non valida']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Verifica l'utente tramite username e token
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND token = ?');
    $stmt->execute([$username, $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se l'utente non esiste o il token è errato
    if (!$user) {
        echo json_encode(['error' => 'Credenziali non valide']);
        exit;
    }

    // Recupera la notifica corrispondente
    $stmt = $pdo->prepare('SELECT * FROM notifications WHERE id_notification = ? AND user_to_id = ?');
    $stmt->execute([$notification_id, $user['id']]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se la notifica non esiste o non appartiene all'utente
    if (!$notification) {
        echo json_encode(['error' => 'Notifica non trovata o non autorizzato']);
        exit;
    }

    // Aggiorna la notifica con la risposta (accepted/rejected)
    $proposal_data = json_decode($notification['proposal_json'], true);
    $proposal_data['status'] = $response;  // Aggiorna lo stato

    $stmt = $pdo->prepare('UPDATE notifications SET proposal_json = ? WHERE id_notification = ?');
    $stmt->execute([json_encode($proposal_data), $notification_id]);

    echo json_encode(['success' => 'Richiesta ' . $response . ' con successo']);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante la risposta: ' . $e->getMessage()]);
}
?>
