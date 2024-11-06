<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';
$permission_id = $_POST['permission_id'] ?? '';
$team_id = $_POST['team_id'] ?? '';  // ID della squadra di arrivo

// Verifica che tutti i campi obbligatori siano stati forniti
if (!$username || !$token || !$permission_id || !$team_id) {
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

    // Se l'utente non esiste o il token è errato
    if (!$user) {
        echo json_encode(['error' => 'Credenziali non valide']);
        exit;
    }

    // Trova l'amministratore della squadra di arrivo in base all'ID della squadra nella tabella user_permissions
    $stmt = $pdo->prepare('
        SELECT up.id_user 
        FROM user_permissions up
        JOIN permissions p ON up.id_permission = p.id_permission
        WHERE up.id_team = ? AND p.name_code = ?
    ');
    $stmt->execute([$team_id, 'admin']);  // Usa 'admin' per cercare l'amministratore
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se la squadra non ha un amministratore associato
    if (!$admin) {
        echo json_encode(['error' => 'Nessun amministratore associato per la squadra con ID ' . $team_id]);
        exit;
    }

    // Controlla se l'utente sta cercando di inviare la notifica a se stesso
    if ($admin['id_user'] == $user['id']) {
        echo json_encode(['error' => 'Non puoi inviare una richiesta a te stesso']);
        exit;
    }

    // Verifica se la richiesta esiste già nella tabella notifications_permissions
    $stmt = $pdo->prepare('
        SELECT * FROM notifications_permissions
        WHERE id_to = ? AND id_from = ? AND id_permission = ? AND id_team = ?
    ');
    $stmt->execute([$admin['id_user'], $user['id'], $permission_id, $team_id]);
    $existing_request = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se esiste già una richiesta
    if ($existing_request) {
        echo json_encode(['error' => 'Richiesta già esistente per questa combinazione']);
        exit;
    }

    // Inserisci la richiesta di permesso nella tabella notifications_permissions
    $stmt = $pdo->prepare('
        INSERT INTO notifications_permissions (id_to, id_from, id_permission, id_team) 
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$admin['id_user'], $user['id'], $permission_id, $team_id]);

    echo json_encode(['success' => 'Richiesta inviata correttamente e inserita in notifications_permissions']);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante l\'invio della richiesta: ' . $e->getMessage()]);
}
?>
