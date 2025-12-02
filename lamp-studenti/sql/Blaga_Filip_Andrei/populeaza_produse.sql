create
    definer = root@`%` procedure Blaga_Filip_Andrei.populeaza_produse()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE categorie_id INT DEFAULT 1;
    WHILE i <= 50 DO
        INSERT INTO produse (denumire, descriere, pret, id_categorie) VALUES
        (CONCAT('Produs ', i),
        CONCAT('Descrierea produsului ', i),
        ROUND(RAND() * (500 - 100) + 100, 2), #functie de rotunjit
        categorie_id);

        SET categorie_id = (categorie_id % 10) + 1;
        SET i = i + 1;
    END WHILE;
END;

