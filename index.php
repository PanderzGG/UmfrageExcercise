<?php
session_start();
require_once 'db.php';

// Prüfen, ob die Session den Key "fragen" enthält
if (!isset($_SESSION['fragenById'])) {
    
    $fragenById = [];

    // Fragen laden
    $result = $db->query("SELECT id, fragentext FROM frage");
    while ($row = $result->fetch_object()) {
        $fragenById[$row->id] = [
            'id' => $row->id,
            'fragentext' => $row->fragentext,
            'moeglicheAntworten' => [],
            'ausgewaehlteAntwortID' => 0
        ];
    }

    // Mögliche Antworten laden
    $result = $db->query("SELECT id, frageid, antworttext FROM moeglicheantwort");
    while ($row = $result->fetch_object()) {
        $antwort = [
            'id' => $row->id,
            'text' => $row->antworttext
        ];
        $fragenById[$row->frageid]['moeglicheAntworten'][] = $antwort;
    }

    $_SESSION['fragenById'] = $fragenById;
}

// Prüfen, ob die Session den Key "fragenindex" enthält
if (!isset($_SESSION['fragenindex'])) {
    $_SESSION['fragenindex'] = 0;
}

// Prüfen, ob der fragenindex größer ist als die Anzahl der Fragen
if ($_SESSION['fragenindex'] >= count($_SESSION['fragenById'])) {
    $_SESSION['fragenindex'] = 0;
}

// Aktuelle Frage
$fragenIndex = $_SESSION['fragenindex'];
$fragenIds = array_keys($_SESSION['fragenById']);
$aktuelleFrageId = $fragenIds[$fragenIndex];
$aktuelleFrage = $_SESSION['fragenById'][$aktuelleFrageId];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fragebogen</title>
</head>
<body>
    <h1>Frage <?php echo $fragenIndex + 1; ?></h1>
    <p><?php echo htmlspecialchars($aktuelleFrage['fragentext']); ?></p>
    <form action="naechste_frage.php" method="post">
        <ul>
            <?php foreach ($aktuelleFrage['moeglicheAntworten'] as $antwort): ?>
                <li>
                    <input type="radio" name="ausgewaehlte_antwort" value="<?php echo $antwort['id']; ?>"
                    <?php if ($aktuelleFrage['ausgewaehlteAntwortID'] == $antwort['id']) echo 'checked'; ?>>
                    <?php echo htmlspecialchars($antwort['text']); ?>
                </li>
            <?php endforeach; ?>
            <li>
                <input type="radio" name="ausgewaehlte_antwort" value="0"
                <?php if ($aktuelleFrage['ausgewaehlteAntwortID'] == 0) echo 'checked'; ?>>
                Keine Antwort
            </li>
        </ul>
        <input type="submit" value="Nächste Frage" />
    </form>
    <a href="vorherige_frage.php">Vorige Frage</a>
</body>
</html>