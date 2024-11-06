<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recupera i dati dall'input POST o GET
$id = $_POST['id'] ?? $_GET['id'] ?? '';
$email = $_POST['email'] ?? $_GET['email'] ?? '';
$hashed_password = $_POST['password'] ?? $_GET['password'] ?? '';

// Verifica che i campi obbligatori siano stati forniti
if (!$id || !$email || !$hashed_password) {
    echo json_encode(['error' => 'Tutti i campi obbligatori devono essere compilati']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Prepara una query per ottenere l'utente in base a ID e email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND email = ?');
    $stmt->execute([$id, $email]);

    // Controlla se l'utente esiste
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Verifica che la password hashata corrisponda
        if ($hashed_password === $user['password']) {
            // Restituisci tutti i dati dell'utente, tranne la password
            unset($user['password']); // Rimuove la password dai dati mostrati
            unset($user['email']); // Rimuove la password dai dati mostrati
            unset($user['phone']);
            unset($user['token']);
            unset($user['token_api']);
            unset($user['name']);
            unset($user['surname']);
            unset($user['id']);
            echo json_encode($user);
        } else {
            echo json_encode(['error' => 'Password hash errata']);
        }
    } else {
        echo json_encode(['error' => 'Utente non trovato']);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Errore nella richiesta: ' . $e->getMessage()]);
}
?>
