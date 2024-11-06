<?php
session_start(); // Avvio della sessione

// Inclusione del file per la connessione al database
include $_SERVER['DOCUMENT_ROOT']."/api/db_entry.php";

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Connessione al database
        $pdo = getDatabaseConnection();

        // Verifica se l'email esiste nel database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verifica se l'account è stato confermato
            if ($user['status'] != 1) {
                $error_message = "Il tuo account non è stato ancora confermato.";
            } else {
                // Verifica della password
                if (password_verify($password, $user['password'])) {
                    // Login riuscito, imposta i dati della sessione
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];

                    // Reindirizza l'utente alla dashboard o a un'altra pagina protetta
                    header("Location: dashboard.php");
                    exit;
                } else {
                    // Password errata
                    $error_message = "Password non corretta.";
                }
            }
        } else {
            // Utente non trovato
            $error_message = "Indirizzo email non trovato.";
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
        <h2>Login</h2>
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Accedi</button>
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
