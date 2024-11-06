<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

$response = [
    "status" => 500,
    "message" => "Errore interno del server."
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['token'], $_POST['id_player'], $_POST['name'], $_POST['surname'], $_POST['id_team'])) {
        $username = $_POST['username'];
        $token = $_POST['token'];
        $id_player = $_POST['id_player'];
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $id_team = $_POST['id_team'];
        $icon = isset($_POST['icon']) ? $_POST['icon'] : null;
        $image = isset($_POST['image']) ? $_POST['image'] : null;
        $ft_id = isset($_POST['ft_id']) ? $_POST['ft_id'] : null;
        $qi = isset($_POST['qi']) ? $_POST['qi'] : null;
        $qa = isset($_POST['qa']) ? $_POST['qa'] : null;

        try {
            // Connessione al database
            $pdo = getDatabaseConnection();

            // Verifica se l'utente è un superadmin
            $stmt = $pdo->prepare("SELECT superadmin FROM users WHERE username = ? AND token = ?");
            $stmt->execute([$username, $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['superadmin'] == 1) {
                // Verifica se esiste già un giocatore con lo stesso nome e cognome nello stesso team (escludendo se stesso)
                $stmt = $pdo->prepare("SELECT id_player FROM players WHERE name = ? AND surname = ? AND id_team = ? AND id_player != ?");
                $stmt->execute([$name, $surname, $id_team, $id_player]);
                $existingPlayer = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingPlayer) {
                    $response["status"] = 409;
                    $response["message"] = "Giocatore con lo stesso nome e cognome già esistente in questo team.";
                } else {
                    // Verifica se esiste un duplicato di nome nella stessa lega (escludendo se stesso)
                    $stmt = $pdo->prepare("SELECT p.id_player FROM players p JOIN teams t ON p.id_team = t.id_team WHERE p.name = ? AND t.id_league = (SELECT id_league FROM teams WHERE id_team = ?) AND p.id_player != ?");
                    $stmt->execute([$name, $id_team, $id_player]);
                    $existingLeaguePlayer = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingLeaguePlayer) {
                        $response["status"] = 409;
                        $response["message"] = "Esiste già un giocatore con lo stesso nome in questa lega.";
                    } else {
                        // Aggiorna i dettagli del giocatore
                        $stmt = $pdo->prepare("UPDATE players SET name = ?, surname = ?, icon = ?, image = ?, ft_id = ?, qi = ?, qa = ?, id_team = ? WHERE id_player = ?");
                        $stmt->execute([$name, $surname, $icon, $image, $ft_id, $qi, $qa, $id_team, $id_player]);

                        $response["status"] = 200;
                        $response["message"] = "Giocatore aggiornato con successo.";
                    }
                }
            } else {
                $response["status"] = 403;
                $response["message"] = "Accesso negato: permessi insufficienti.";
            }

        } catch (PDOException $e) {
            $response["message"] = "Errore: " . $e->getMessage();
        }
    } else {
        $response["message"] = "Parametri mancanti: username, token, id_player, name, surname o id_team non forniti.";
    }
} else {
    $response["message"] = "Metodo di richiesta non valido. Usa POST.";
}

echo json_encode($response);
?>
