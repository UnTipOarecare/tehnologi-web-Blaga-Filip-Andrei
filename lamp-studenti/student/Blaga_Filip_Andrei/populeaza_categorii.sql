create
    definer = root@`%` procedure Blaga_Filip_Andrei.populeaza_categorii()
BEGIN
    DECLARE i INT DEFAULT 1;
    WHILE i <= 50 DO
        INSERT INTO categorii (denumire) VALUES
        (CONCAT('Categorie ', i)); #CONCAT lipeste un string (text) si variabila i aici
        SET i = i + 1; #updatam variabila i
    END WHILE;
END;

