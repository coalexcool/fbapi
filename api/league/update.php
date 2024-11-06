 <?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

$response = [
    "status" => 500,
    "message" => "Errore interno del server."
];

// Verifica che l'utente sia un superadmin e che siano forniti username e token
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['token'], $_POST['id_league'], $_POST['name'])) {
        $username = $_POST['username'];
        $token = $_POST['token'];
        $id_league = $_POST['id_league'];
        $name = $_POST['name'];
        $icon = isset($_POST['icon']) ? $_POST['icon'] : null;
        $image = isset($_POST['image']) ? $_POST['image'] : null;

        try {
            // Connessione al database
            $pdo = getDatabaseConnection();

            // Verifica se l'utente è un superadmin
            $stmt = $pdo->prepare("SELECT superadmin FROM users WHERE username = ? AND token = ?");
            $stmt->execute([$username, $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['superadmin'] == 1) {
                // Aggiorna la lega
                $stmt = $pdo->prepare("UPDATE leagues SET name = ?, icon = ?, image = ? WHERE id_league = ?");
                $stmt->execute([$name, $icon, $image, $id_league]);

                if ($stmt->rowCount()) {
                    $response["status"] = 200;
                    $response["message"] = "Lega aggiornata con successo.";
                } else {
                    $response["message"] = "Nessuna modifica rilevata o lega non trovata.";
                }
            } else {
                $response["status"] = 403;
                $response["message"] = "Accesso negato: permessi insufficienti.";
            }

        } catch (PDOException $e) {
            $response["message"] = "Errore: " . $e->getMessage();
        }
    } else {
        $response["message"] = "Parametri mancanti: username, token, id_league o name non forniti.";
    }
} else {
    $response["message"] = "Metodo di richiesta non valido. Usa POST.";
}

echo json_encode($response);
?>
