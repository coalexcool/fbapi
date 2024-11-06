<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo i dati inviati tramite POST
$username = $_POST['username'] ?? '';
$token = $_POST['token'] ?? '';
$user_to_id = $_POST['user_to_id'] ?? '';
$proposal_json = $_POST['proposal_json'] ?? '';

// Verifica che tutti i campi obbligatori siano stati forniti
if (!$username || !$token || !$user_to_id || !$proposal_json) {
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

    // Determina il ruolo dell'utente
    $user_role = $user['role'];  // Supponiamo che il ruolo sia salvato nella colonna 'role'

    // Variabile per memorizzare il from_user_id
    $user_from_id = null;

    if ($user_role == 'direttore_sportivo') {
        // Se l'utente è un direttore_sportivo, trova l'amministratore della sua squadra
        $stmt = $pdo->prepare('SELECT admin_user_id FROM teams WHERE id_team = ?');
        $stmt->execute([$user['id_team']]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($team) {
            $user_from_id = $team['admin_user_id'];
        } else {
            echo json_encode(['error' => 'Squadra non trovata']);
            exit;
        }
    } elseif ($user_role == 'amministratore') {
        // Se l'utente è un amministratore, trova l'amministratore della squadra di destinazione
        $stmt = $pdo->prepare('SELECT admin_user_id FROM teams WHERE id_team = (SELECT id_team FROM users WHERE id = ?)');
        $stmt->execute([$user_to_id]);
        $team = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($team) {
            $user_from_id = $team['admin_user_id'];
        } else {
            echo json_encode(['error' => 'Squadra di destinazione non trovata']);
            exit;
        }
    } else {
        // Se il ruolo non è né direttore_sportivo né amministratore, restituisci errore
        echo json_encode(['error' => 'Ruolo non autorizzato']);
        exit;
    }

    // Inserisci la notifica nella tabella
    $stmt = $pdo->prepare('INSERT INTO notifications (user_from_id, user_to_id, proposal_json) VALUES (?, ?, ?)');
    $stmt->execute([$user_from_id, $user_to_id, $proposal_json]);

    echo json_encode(['success' => 'Notifica inviata correttamente']);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore durante l\'invio della notifica: ' . $e->getMessage()]);
}
?>
