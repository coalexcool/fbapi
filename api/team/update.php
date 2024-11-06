<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

$response = [
    "status" => 500,
    "message" => "Errore interno del server."
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['token'], $_POST['id_team'], $_POST['name'])) {
        $username = $_POST['username'];
        $token = $_POST['token'];
        $id_team = $_POST['id_team'];
        $name = $_POST['name'];
        $icon = isset($_POST['icon']) ? $_POST['icon'] : null;
        $image = isset($_POST['image']) ? $_POST['image'] : null;
        $amount = isset($_POST['amount']) ? $_POST['amount'] : null;
        $id_league = isset($_POST['id_league']) ? $_POST['id_league'] : null;

        try {
            // Connessione al database
            $pdo = getDatabaseConnection();

            // Verifica se l'utente è un superadmin
            $stmt = $pdo->prepare("SELECT superadmin FROM users WHERE username = ? AND token = ?");
            $stmt->execute([$username, $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['superadmin'] == 1) {
                // Aggiorna i dettagli del team
                $stmt = $pdo->prepare("UPDATE teams SET name = ?, icon = ?, image = ?, amount = ?, id_league = ? WHERE id_team = ?");
                $stmt->execute([$name, $icon, $image, $amount, $id_league, $id_team]);

                if ($stmt->rowCount()) {
                    $response["status"] = 200;
                    $response["message"] = "Team aggiornato con successo.";
                } else {
                    $response["message"] = "Nessuna modifica rilevata o team non trovato.";
                }
            } else {
                $response["status"] = 403;
                $response["message"] = "Accesso negato: permessi insufficienti.";
            }

        } catch (PDOException $e) {
            $response["message"] = "Errore: " . $e->getMessage();
        }
    } else {
        $response["message"] = "Parametri mancanti: username, token, id_team o name non forniti.";
    }
} else {
    $response["message"] = "Metodo di richiesta non valido. Usa POST.";
}

echo json_encode($response);
?>
