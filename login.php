<?php
session_start();

// Verbindung zur Datenbank mit PDO herstellen
try {
    $dsn = "mysql:host=localhost;dbname=bfw_kleinanzeigen;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $passwort = $_POST['passwort'] ?? '';

    if (!empty($email) && !empty($passwort)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM benutzer WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $benutzer = $stmt->fetch();

            if ($benutzer && password_verify($passwort, $benutzer['passwort'])) {
                $_SESSION['benutzer_id'] = $benutzer['id'];
                $_SESSION['nickname'] = $benutzer['nickname'];
                $_SESSION['rolle'] = $benutzer['rolle'] ?? 'user';
                
                
                if ($_SESSION['rolle'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: kleinanzeigen.php");
                }
                exit;
            } else {
                $message = "E-Mail oder Passwort ist falsch.";
            }
        } catch (PDOException $e) {
            $message = "Fehler bei der Anmeldung: " . $e->getMessage();
        }
    } else {
        $message = "Bitte E-Mail und Passwort eingeben.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Login</title>
</head>
<body>
    <div class="header">
        <div class="item1"><a href="kleinanzeigen.php"><img src="cropped-bfw-hamburg-Logo-4c.png" width="75%"></a></div>
        <div class="item2">Willkommen auf BfW-Kleinanzeigen</div>
        <div class="navbar">
            <button class="menu-button">☰ Menü</button>
            <div class="dropdown-content">
                <a href="kleinanzeigen.php">Startseite</a>
                <a href="konfig.php">Anzeigen</a>
                <a href="kategorien.php">Kategorien</a>
                <a href="meine_anzeigen.php">Meine Anzeigen</a>
                <a href="login.php">Login</a>
                <a href="register.php">Registrieren</a>
            </div>
        </div>
    </div>

    <div class="login-form">
        <h2>Login</h2>
        <?php if (!empty($message)): ?>
            <p class="error"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="email">E-Mail:</label>
            <input type="email" name="email" id="email" required>
            <label for="passwort">Passwort:</label>
            <input type="password" name="passwort" id="passwort" required>
            <button type="submit">Anmelden</button>
        </form>
    </div>
</body>
</html>