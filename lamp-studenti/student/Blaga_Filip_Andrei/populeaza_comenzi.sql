create
    definer = root@`%` procedure Blaga_Filip_Andrei.populeaza_comenzi()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE user_id INT DEFAULT 1;
    WHILE i <= 50 DO
        INSERT INTO comenzi (id_utilizator, status) VALUES
        (user_id, IF(i % 2 = 0, 'plasata', 'livrata')); #o livrare va fi livrata si urmatoarea va aparea plasata si se repeta

        SET user_id = (user_id % 50) + 1;
        SET i = i + 1;
    END WHILE;
END;

