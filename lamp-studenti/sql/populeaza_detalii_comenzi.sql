create
    definer = root@`%` procedure Calorifere.populeaza_detalii_comenzi()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE comanda_id INT DEFAULT 1;
    DECLARE produs_id INT DEFAULT 1;
    DECLARE cantitate INT DEFAULT 1;

    WHILE i <= 50 DO
        SET produs_id = FLOOR(RAND() * 50) + 1;
        SET cantitate = FLOOR(RAND() * 5) + 1;

        INSERT INTO detalii_comenzi (id_comanda, id_produs, cantitate)
        VALUES (comanda_id, produs_id, cantitate);

        SET comanda_id = (comanda_id % 50) + 1;
        SET i = i + 1;
    END WHILE;
END;

