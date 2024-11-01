insert into frage(fragentext) values
('Was war die Farbe des weißen Rosses Heinrichs IV.?'),
('Wer sagte \"Sammelt euch um meinen weißen Panasch\"?'),
('Was war Ravaillac?');


insert into moeglicheantwort(frageid,antworttext) values
(1,'Blau'),
(1,'Grau'),
(1,'Weiß'),
(2,'Heinrich IV.'),
(2,'Friedrich I.'),
(2,'Sankt Aloysius'),
(3,'Eine amerikanische Automarke'),
(3,'Der Mörder Heinrichs IV.'),
(3,'Ein berühmter Ort bei Bordeaux');


INSERT INTO abgegebeneantwort (id, nutzertokenid, frageid, antwortid) VALUES
(NULL, 1, 1, 1),
(NULL, 1, 2, 4),
(NULL, 1, 3, 7),
(NULL, 2, 1, 2),
(NULL, 2, 2, 5),
(NULL, 2, 3, 8),
(NULL, 3, 1, 3),
(NULL, 3, 2, 6),
(NULL, 4, 2, 4),
(NULL, 4, 3, 5);

