<?php
// Inclusione del file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

// Funzione per generare un token casuale
function generateToken($length = 50) {
    return bin2hex(random_bytes($length / 2));
}

// Funzione per inviare l'email di conferma
function sendConfirmationEmail($email, $token) {
    // Recupera il protocollo e l'host attuale
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $confirmation_link = $protocol . "://" . $host . "/confirm.php?token=" . $token;
    
    $subject = "Conferma la tua registrazione";
    $message = "Clicca sul link per confermare la tua registrazione: " . $confirmation_link;
    $headers = "From: no-reply@yourwebsite.com\r\n";
    
    // Usa mail() o una libreria come PHPMailer
    mail($email, $subject, $message, $headers);
}

// Gestione della registrazione
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Raccogli i dati dal form
    $username = $_POST['username'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $token = generateToken();

    try {
        // Connessione al database
        $pdo = getDatabaseConnection();

        // Controlla se l'email o l'username sono già in uso
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            // Email o username già esistono
            if ($existingUser['email'] === $email) {
                $error_message = "L'email è già in uso.";
            } elseif ($existingUser['username'] === $username) {
                $error_message = "Il nome utente è già in uso.";
            }
        } else {
            // Inserimento dei dati dell'utente nel database
            $stmt = $pdo->prepare("INSERT INTO users (username, name, surname, phone, email, password, token, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$username, $name, $surname, $phone, $email, $password, $token]);

            // Invio email di conferma
            sendConfirmationEmail($email, $token);

            echo "Registrazione avvenuta con successo. Controlla la tua email per confermare.";
        }
    } catch (PDOException $e) {
        echo "Errore: " . $e->getMessage();
    }
}
?>

<!-- header -->
<?php include $_SERVER['DOCUMENT_ROOT']."/components/header.php";?>

<body>
    <!-- Navbar -->
    <?php include $_SERVER['DOCUMENT_ROOT']."/components/navbar.php";?>

    <div class="container mt-5">
        <h2>Registrazione</h2>
        <form method="POST" action="signup.php">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Nome</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="surname" class="form-label">Cognome</label>
                <input type="text" class="form-control" id="surname" name="surname" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Telefono</label>
                <input type="text" class="form-control" id="phone" name="phone">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrati</button>
        </form>
    </div>

    <!-- Toast Notification -->
    <?php if (!empty($error_message)): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="toast" class="toast show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $error_message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <?php include $_SERVER['DOCUMENT_ROOT']."/components/footer.php";?>
</body>
</html>
