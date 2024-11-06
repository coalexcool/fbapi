<?php
// Includi il file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Crea una connessione al database
$pdo = getDatabaseConnection();

try {
    // Query per ottenere tutti i permessi dalla tabella permissions
    $stmt = $pdo->prepare('SELECT * FROM permissions');
    $stmt->execute();
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Restituisce i dati in formato JSON
    echo json_encode(['success' => true, 'data' => $permissions]);

} catch (PDOException $e) {
    // In caso di errore restituisce un messaggio d'errore
    echo json_encode(['success' => false, 'error' => 'Errore durante il recupero dei dati: ' . $e->getMessage()]);
}
?>
