<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

$response = [
    "status" => 500,
    "message" => "Errore interno del server."
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['token'], $_POST['name'], $_POST['id_league'])) {
        $username = $_POST['username'];
        $token = $_POST['token'];
        $name = $_POST['name'];
        $id_league = $_POST['id_league'];
        $icon = isset($_POST['icon']) ? $_POST['icon'] : null;
        $image = isset($_POST['image']) ? $_POST['image'] : null;
        $amount = isset($_POST['amount']) ? $_POST['amount'] : null;

        try {
            // Connessione al database
            $pdo = getDatabaseConnection();

            // Verifica se l'utente è un superadmin
            $stmt = $pdo->prepare("SELECT superadmin FROM users WHERE username = ? AND token = ?");
            $stmt->execute([$username, $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['superadmin'] == 1) {
                // Verifica se esiste già un team associato alla stessa id_league
                $stmt = $pdo->prepare("SELECT id_team FROM teams WHERE id_league = ?");
                $stmt->execute([$id_league]);
                $existingTeam = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingTeam) {
                    // Se esiste già un team per la lega, ritorna un errore
                    $response["status"] = 409;
                    $response["message"] = "Esiste già un team per questa lega (id_league = $id_league).";
                } else {
                    // Inserisci il nuovo team
                    $stmt = $pdo->prepare("INSERT INTO teams (name, icon, image, amount, id_league) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $icon, $image, $amount, $id_league]);

                    $response["status"] = 200;
                    $response["message"] = "Team creato con successo.";
                }
            } else {
                $response["status"] = 403;
                $response["message"] = "Accesso negato: permessi insufficienti.";
            }

        } catch (PDOException $e) {
            $response["message"] = "Errore: " . $e->getMessage();
        }
    } else {
        $response["message"] = "Parametri mancanti: username, token, name o id_league non forniti.";
    }
} else {
    $response["message"] = "Metodo di richiesta non valido. Usa POST.";
}

echo json_encode($response);
?>
