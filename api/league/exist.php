<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo il parametro 'name' dal GET
$league_name = $_GET['name'] ?? null;

// Verifica se 'name' è stato fornito
if (!$league_name) {
    echo json_encode(['error' => 'Il nome della lega è obbligatorio']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Prepara la query per verificare se il nome della lega esiste e recupera l'id della lega
    $stmt = $pdo->prepare('SELECT id_league FROM leagues WHERE name = ?');
    $stmt->execute([$league_name]);
    $league = $stmt->fetch(PDO::FETCH_ASSOC);

    // Controlla se la lega esiste
    if ($league) {
        echo json_encode(['exists' => true, 'id_league' => $league['id_league'], 'message' => 'La lega esiste nel database.']);
    } else {
        echo json_encode(['exists' => false, 'message' => 'La lega non esiste nel database.']);
    }
} catch (PDOException $e) {
    // Gestisce eventuali errori di connessione o query
    echo json_encode(['error' => 'Errore durante il controllo della lega: ' . $e->getMessage()]);
}
?>
