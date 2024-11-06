<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Recuperiamo il parametro 'ft_id' dal GET
$ft_id = $_GET['ft_id'] ?? null;

// Verifica se 'ft_id' è stato fornito
if (!$ft_id) {
    echo json_encode(['error' => 'ft_id è obbligatorio']);
    exit;
}

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Prepara la query per verificare se 'ft_id' esiste e recupera l'id del giocatore
    $stmt = $pdo->prepare('SELECT id_player FROM players WHERE ft_id = ?');
    $stmt->execute([$ft_id]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);

    // Controlla se il giocatore esiste
    if ($player) {
        echo json_encode(['exists' => true, 'id_player' => $player['id_player'], 'message' => 'Il giocatore esiste nel database.']);
    } else {
        echo json_encode(['exists' => false, 'message' => 'Il giocatore non esiste nel database.']);
    }
} catch (PDOException $e) {
    // Gestisce eventuali errori di connessione o query
    echo json_encode(['error' => 'Errore durante il controllo del giocatore: ' . $e->getMessage()]);
}
?>
