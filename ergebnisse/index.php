<?php
require_once '../db.php'; // Angenommen, die Datenbankverbindung ist eine Ebene höher

// Fragen laden und in Map speichern
$fragen = [];
$fragenById = [];

$result = $db->query("SELECT id, fragentext FROM frage");
while ($row = $result->fetch_assoc()) {
    $frageId = $row['id'];
    $frage = [
        'id' => $frageId,
        'fragentext' => $row['fragentext'],
        'antworten' => [],  // Wird später gefüllt
    ];
    $fragen[] = $frage;
    $fragenById[$frageId] = &$fragen[count($fragen) - 1];
}

// Mögliche Antworten laden und Anzahl der abgegebenen Antworten ermitteln
$result = $db->query("
    SELECT 
        ma.id AS antwortid, 
        ma.frageid, 
        ma.antworttext, 
        COUNT(aa.id) AS anzahl
    FROM 
        moeglicheantwort ma
    LEFT JOIN 
        abgegebeneantwort aa ON ma.id = aa.antwortid
    GROUP BY 
        ma.id
");

while ($row = $result->fetch_assoc()) {
    $frageId = $row['frageid'];
    $antwort = [
        'id' => $row['antwortid'],
        'antworttext' => $row['antworttext'],
        'anzahl' => $row['anzahl'],
    ];
    $fragenById[$frageId]['antworten'][] = $antwort;
}

// Gesamte Anzahl Nutzertoken laden
$result = $db->query("SELECT COUNT(*) AS nutzeranzahl FROM nutzertoken");
$row = $result->fetch_assoc();
$gesamtNutzer = $row['nutzeranzahl'];

// Anzahl Antworten pro Frage und Anzahl "Keine Antwort" berechnen
foreach ($fragen as &$frage) {
    $frageId = $frage['id'];
    // Anzahl abgegebene Antworten für die Frage
    $result = $db->query("
        SELECT COUNT(DISTINCT nutzertokenid) AS anzahl 
        FROM abgegebeneantwort 
        WHERE frageid = $frageId AND antwortid != 0
    ");
    $row = $result->fetch_assoc();
    $antwortGesamt = $row['anzahl'];

    // Anzahl "Keine Antwort"
    $keineAntwortAnzahl = $gesamtNutzer - $antwortGesamt;

    // Speichern
    $frage['antwortGesamt'] = $antwortGesamt;
    $frage['keineAntwortAnzahl'] = $keineAntwortAnzahl;
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Umfrageergebnisse</title>
</head>
<body>
    <h1>Umfrageergebnisse</h1>
    <p>Gesamte Anzahl Nutzertoken: <?php echo $gesamtNutzer; ?></p>
    
    <?php foreach ($fragen as $frage): ?>
        <h2><?php echo htmlspecialchars($frage['fragentext']); ?></h2>
        <p>Gesamte Anzahl Antworten: <?php echo $frage['antwortGesamt']; ?></p>
        <ul>
            <?php foreach ($frage['antworten'] as $antwort): ?>
                <?php
                    $prozent = $gesamtNutzer > 0 ? ($antwort['anzahl'] / $gesamtNutzer) * 100 : 0;
                ?>
                <li>
                    <?php echo htmlspecialchars($antwort['antworttext']); ?>: 
                    <?php echo $antwort['anzahl']; ?> 
                    (<?php echo number_format($prozent, 2); ?>%)
                </li>
            <?php endforeach; ?>
            <?php
                $keineAntwortProzent = $gesamtNutzer > 0 ? ($frage['keineAntwortAnzahl'] / $gesamtNutzer) * 100 : 0;
            ?>
            <li>
                Keine Antwort: <?php echo $frage['keineAntwortAnzahl']; ?> 
                (<?php echo number_format($keineAntwortProzent, 2); ?>%)
            </li>
        </ul>
    <?php endforeach; ?>
    
    <p><a href="json_export.php">Ergebnisse als JSON herunterladen</a></p>
</body>
</html>