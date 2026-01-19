create
    definer = root@`%` procedure Calorifere.populeaza_recenzii()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE produs_id INT DEFAULT 1;
    DECLARE user_id INT DEFAULT 1;
    WHILE i <= 50 DO
        INSERT INTO recenzii (id_produs, id_utilizator, rating, comentariu) VALUES
        (produs_id, user_id, FLOOR(RAND() * 5) + 1,
        CONCAT('Comentariu pentru produsul ', produs_id, ' de la utilizatorul ', user_id));

        SET produs_id = (produs_id % 50) + 1;
        SET user_id = (user_id % 50) + 1;
        SET i = i + 1;
    END WHILE;
END;

