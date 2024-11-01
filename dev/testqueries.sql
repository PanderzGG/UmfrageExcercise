SELECT fragentext FROM frage;

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
        ma.id;

