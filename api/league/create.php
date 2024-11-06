<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

$response = [
    "status" => 500,
    "message" => "Errore interno del server."
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['token'], $_POST['name'])) {
        $username = $_POST['username'];
        $token = $_POST['token'];
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
                // Inserisci la nuova lega
                $stmt = $pdo->prepare("INSERT INTO leagues (name, icon, image) VALUES (?, ?, ?)");
                $stmt->execute([$name, $icon, $image]);

                // Ottieni l'ID della lega appena creata
                $id_league = $pdo->lastInsertId();

                $response["status"] = 200;
                $response["message"] = "Lega creata con successo.";
                $response["id_league"] = $id_league;  // Restituisce l'id della nuova lega
            } else {
                $response["status"] = 403;
                $response["message"] = "Accesso negato: permessi insufficienti.";
            }

        } catch (PDOException $e) {
            $response["message"] = "Errore: " . $e->getMessage();
        }
    } else {
        $response["message"] = "Parametri mancanti: username, token o name non forniti.";
    }
} else {
    $response["message"] = "Metodo di richiesta non valido. Usa POST.";
}

echo json_encode($response);
?>
