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

try {
    $stmt = $pdo->query("SELECT * FROM rubriken ORDER BY name ASC");
    $rubriken = $stmt->fetchAll();
} catch (PDOException $e) {
    $rubriken = [];
    $message = "Fehler beim Abrufen der Rubriken: " . $e->getMessage();
}

$anzeigen = [];
$aktuelleRubrik = null;
if (isset($_GET['rubrik_id'])) {
    $rubrik_id = (int)$_GET['rubrik_id'];

    try {
        $stmt = $pdo->prepare("SELECT name FROM rubriken WHERE id = :rubrik_id");
        $stmt->execute([':rubrik_id' => $rubrik_id]);
        $aktuelleRubrik = $stmt->fetchColumn();
    } catch (PDOException $e) {
        $message = "Fehler beim Abrufen der Rubrik: " . $e->getMessage();
    }

    if ($aktuelleRubrik) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM anzeigen WHERE rubrik_id = :rubrik_id AND status = 'freigegeben' ORDER BY erstellt_am DESC");
            $stmt->execute([':rubrik_id' => $rubrik_id]);
            $anzeigen = $stmt->fetchAll();
        } catch (PDOException $e) {
            $message = "Fehler beim Abrufen der Anzeigen: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Kategorien</title>
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
        <p class="error"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="content">
        <div class="rubriken">
            <h2>Kategorien</h2>
            <?php foreach ($rubriken as $rubrik): ?>
                <a href="kategorien.php?rubrik_id=<?= htmlspecialchars($rubrik['id']) ?>"
                   class="rubrik-link <?= isset($rubrik_id) && $rubrik['id'] == $rubrik_id ? 'active' : '' ?>">
                    <?= htmlspecialchars($rubrik['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($aktuelleRubrik): ?>
            <div class="anzeigen">
                <h2>Anzeigen in Rubrik: <?= htmlspecialchars($aktuelleRubrik) ?></h2>
                <?php if (!empty($anzeigen)): ?>
                    <?php foreach ($anzeigen as $anzeige): ?>
                        <div class="anzeige">
                            <h3><?= htmlspecialchars($anzeige['titel']) ?></h3>
                            <p><strong>Nickname:</strong> <?= htmlspecialchars($anzeige['nickname']) ?></p>
                            <p><strong>E-Mail:</strong> <?= htmlspecialchars($anzeige['email']) ?></p>
                            <p><?= htmlspecialchars($anzeige['beschreibung']) ?></p>
                            <?php if ($anzeige['bild']): ?>
                                <img src="uploads/<?= htmlspecialchars($anzeige['bild']) ?>" alt="Anzeigenbild" style="max-width: 200px;">
                            <?php endif; ?>
                            <p><small>Erstellt am: <?= htmlspecialchars($anzeige['erstellt_am']) ?></small></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Keine Anzeigen in dieser Kategorie vorhanden.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>