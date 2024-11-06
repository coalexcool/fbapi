<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo il parametro 'name' dal GET
$team_name = $_GET['name'] ?? null;

// Verifica se 'name' è stato fornito
if (!$team_name) {
    echo json_encode(['error' => 'Il nome della squadra è obbligatorio']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Prepara la query per verificare se il nome della squadra esiste e recupera l'id della squadra
    $stmt = $pdo->prepare('SELECT id_team FROM teams WHERE name = ?');
    $stmt->execute([$team_name]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    // Controlla se la squadra esiste
    if ($team) {
        echo json_encode(['exists' => true, 'id_team' => $team['id_team'], 'message' => 'La squadra esiste nel database.']);
    } else {
        echo json_encode(['exists' => false, 'message' => 'La squadra non esiste nel database.']);
    }
} catch (PDOException $e) {
    // Gestisce eventuali errori di connessione o query
    echo json_encode(['error' => 'Errore durante il controllo della squadra: ' . $e->getMessage()]);
}
?>
