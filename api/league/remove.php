<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

$response = [
    "status" => 500,
    "message" => "Errore interno del server."
];

// Verifica che l'utente sia un superadmin e che siano forniti username e token
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['token'], $_POST['id_league'])) {
        $username = $_POST['username'];
        $token = $_POST['token'];
        $id_league = $_POST['id_league'];

        try {
            // Connessione al database
            $pdo = getDatabaseConnection();

            // Verifica se l'utente è un superadmin
            $stmt = $pdo->prepare("SELECT superadmin FROM users WHERE username = ? AND token = ?");
            $stmt->execute([$username, $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['superadmin'] == 1) {
                // Elimina la lega
                $stmt = $pdo->prepare("DELETE FROM leagues WHERE id_league = ?");
                $stmt->execute([$id_league]);

                if ($stmt->rowCount()) {
                    $response["status"] = 200;
                    $response["message"] = "Lega rimossa con successo.";
                } else {
                    $response["message"] = "Lega non trovata.";
                }
            } else {
                $response["status"] = 403;
                $response["message"] = "Accesso negato: permessi insufficienti.";
            }

        } catch (PDOException $e) {
            $response["message"] = "Errore: " . $e->getMessage();
        }
    } else {
        $response["message"] = "Parametri mancanti: username, token o id_league non forniti.";
    }
} else {
    $response["message"] = "Metodo di richiesta non valido. Usa POST.";
}

echo json_encode($response);
?>
