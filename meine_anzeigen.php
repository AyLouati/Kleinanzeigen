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


if (!isset($_SESSION['benutzer_id'])) {
    header("Location: login.php?redirect=meine_anzeigen.php");
    exit;
}

$message = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_ad'])) {
    $anzeige_id = (int)$_POST['anzeige_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM anzeigen WHERE id = :anzeige_id AND benutzer_id = :benutzer_id");
        $stmt->execute([':anzeige_id' => $anzeige_id, ':benutzer_id' => $_SESSION['benutzer_id']]);
        $message = "Anzeige erfolgreich gelöscht!";
    } catch (PDOException $e) {
        $message = "Fehler beim Löschen: " . $e->getMessage();
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_ad'])) {
    $anzeige_id = (int)$_POST['anzeige_id'];
    $titel = $_POST['titel'];
    $beschreibung = $_POST['beschreibung'];
    $preis = floatval($_POST['preis']);
    try {
        $stmt = $pdo->prepare("UPDATE anzeigen SET titel = :titel, beschreibung = :beschreibung, preis = :preis WHERE id = :anzeige_id AND benutzer_id = :benutzer_id");
        $stmt->execute([
            ':titel' => $titel,
            ':beschreibung' => $beschreibung,
            ':preis' => $preis,
            ':anzeige_id' => $anzeige_id,
            ':benutzer_id' => $_SESSION['benutzer_id']
        ]);
        $message = "Anzeige erfolgreich bearbeitet!";
    } catch (PDOException $e) {
        $message = "Fehler beim Bearbeiten: " . $e->getMessage();
    }
}


try {
    $stmt = $pdo->prepare("SELECT * FROM anzeigen WHERE benutzer_id = :benutzer_id ORDER BY erstellungsdatum DESC");
    $stmt->execute([':benutzer_id' => $_SESSION['benutzer_id']]);
    $anzeigen = $stmt->fetchAll();
} catch (PDOException $e) {
    $anzeigen = [];
    $message = "Fehler beim Abrufen der Anzeigen: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Meine Anzeigen</title>
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
                <a href="meine_anzeigen.php">Meine Anzeigen</a>
                <?php if (isset($_SESSION['rolle']) && $_SESSION['rolle'] == 'admin'): ?>
                    <a href="admin.php">Admin-Bereich</a>
                <?php endif; ?>
                <a href="login.php">Login</a>
                <a href="register.php">Registrieren</a>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <p class="<?= strpos($message, 'erfolgreich') !== false ? 'success' : 'error' ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="content">
        <h2>Meine Anzeigen</h2>
        <?php if (!empty($anzeigen)): ?>
            <?php foreach ($anzeigen as $anzeige): ?>
                <div class="anzeige">
                    <h3><?= htmlspecialchars($anzeige['titel']) ?></h3>
                    <p><strong>Preis:</strong> €<?= number_format($anzeige['preis'], 2, ',', '.') ?></p>
                    <p><?= htmlspecialchars($anzeige['beschreibung']) ?></p>
                    <?php if ($anzeige['bild']): ?>
                        <img src="Uploads/<?= htmlspecialchars($anzeige['bild']) ?>" alt="Anzeigenbild" style="max-width: 200px;">
                    <?php endif; ?>
                    <p><small>Erstellt am: <?= htmlspecialchars($anzeige['erstellungsdatum']) ?></small></p>
                   
                    <form method="post">
                        <input type="hidden" name="anzeige_id" value="<?= $anzeige['id'] ?>">
                        <label for="titel">Titel:</label>
                        <input type="text" name="titel" value="<?= htmlspecialchars($anzeige['titel']) ?>" required>
                        <label for="beschreibung">Beschreibung:</label>
                        <textarea name="beschreibung" required><?= htmlspecialchars($anzeige['beschreibung']) ?></textarea>
                        <label for="preis">Preis (€):</label>
                        <input type="number" name="preis" value="<?= htmlspecialchars($anzeige['preis']) ?>" step="0.01" min="0" required>
                        <button type="submit" name="edit_ad">Bearbeiten</button>
                    </form>
                   
                    <form method="post">
                        <input type="hidden" name="anzeige_id" value="<?= $anzeige['id'] ?>">
                        <button type="submit" name="delete_ad" onclick="return confirm('Anzeige wirklich löschen?')">Löschen</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Du hast keine Anzeigen.</p>
        <?php endif; ?>
    </div>
</body>
</html>