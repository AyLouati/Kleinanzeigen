<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

try {
    $dsn = "mysql:host=localhost;dbname=bfw_kleinanzeigen;charset=utf8mb4";
    $pdo = new PDO($dsn, "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
}

if (!isset($_SESSION['benutzer_id'])) {
    header("Location: login.php");
    exit;
}

try {
    $stmt = $pdo->query("SELECT * FROM rubriken ORDER BY name ASC");
    $rubriken = $stmt->fetchAll();
} catch (PDOException $e) {
    $rubriken = [];
    $message = "Fehler beim Abrufen der Rubriken: " . $e->getMessage();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aufgeben'])) {
    $titel = $_POST['titel'];
    $beschreibung = $_POST['beschreibung'];
    $rubrik_id = (int)$_POST['rubrik_id'];
    $nickname = $_POST['nickname'];
    $email = $_POST['email'];
    $benutzer_id = $_SESSION['benutzer_id'];
    $bild = null;


    if (isset($_FILES['bild']) && $_FILES['bild']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['bild']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $bild = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['bild']['tmp_name'], "uploads/$bild");
        } else {
            $message = "Nur JPG, JPEG, PNG erlaubt.";
        }
    }

    try {
        $sql = "INSERT INTO anzeigen (titel, beschreibung, rubrik_id, nickname, email, benutzer_id, bild, status)
                VALUES (:titel, :beschreibung, :rubrik_id, :nickname, :email, :benutzer_id, :bild, 'ausstehend')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':titel' => $titel,
            ':beschreibung' => $beschreibung,
            ':rubrik_id' => $rubrik_id,
            ':nickname' => $nickname,
            ':email' => $email,
            ':benutzer_id' => $benutzer_id,
            ':bild' => $bild
        ]);
        $message = "Anzeige erfolgreich aufgegeben! Wird nach Freigabe sichtbar.";
    } catch (PDOException $e) {
        $message = "Fehler beim Erstellen der Anzeige: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Anzeigen aufgeben</title>
</head>
<body>
    <div class="header">
        <div class="item1"><a href="kleinanzeigen.php"><img src="cropped-bfw-hamburg-Logo-4c.png" width="75%"></a></div>
        <div class="item2">Willkommen auf BfW-Kleinanzeigen</div>
        <div class="navbar">
            <button class="menu-button">☰ Menü</button>
            <div class="dropdown-content">
                <a href="kleinanzeigen.php">Startseite</a>
                <a href="anzeigen.php">Anzeigen</a>
                <a href="kategorien.php">Kategorien</a>
                <a href="meine_anzeigen.php">Meine Anzeigen</a>
                <?php if (isset($_SESSION['rolle']) && $_SESSION['rolle'] == 'admin'): ?>
                    <a href="admin.php">Admin-Bereich</a>
                <?php endif; ?>
                <a href="login.php">Login</a>
                <a href="register.php">Registrieren</a>
            </div>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <p class="<?= strpos($message, 'erfolgreich') !== false ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="startseite">
        <div class="Anzeige_aufgeben">
            <div class="item9">
                <form method="post" enctype="multipart/form-data">
                    <label for="titel">Titel:</label><br>
                    <input type="text" id="titel" name="titel" required><br><br>
                    <label for="beschreibung">Beschreibung:</label><br>
                    <textarea id="beschreibung" name="beschreibung" required></textarea><br><br>
                    <label for="rubrik_id">Rubrik:</label><br>
                    <select id="rubrik_id" name="rubrik_id" required>
                        <?php foreach ($rubriken as $rubrik): ?>
                            <option value="<?= $rubrik['id'] ?>"><?= htmlspecialchars($rubrik['name']) ?></option>
                        <?php endforeach; ?>
                    </select><br><br>
                    <label for="nickname">Nickname:</label><br>
                    <input type="text" id="nickname" name="nickname" value="<?= htmlspecialchars($_SESSION['nickname']) ?>" required><br><br>
                    <label for="email">E-Mail:</label><br>
                    <input type="email" id="email" name="email" required><br><br>
                    <label for="bild">Bild (optional):</label><br>
                    <input type="file" id="bild" name="bild" accept=".jpg,.jpeg,.png"><br><br>
                    <button type="submit" name="aufgeben">Anzeige aufgeben</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>