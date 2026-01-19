create
    definer = root@`%` procedure Calorifere.populeaza_users()
BEGIN
    DECLARE i INT DEFAULT 1;
    WHILE i <= 50 DO
        INSERT INTO user (username, parola, nume, email, rol) VALUES
        (CONCAT('user', i),
        SHA2(CONCAT('parola', i), 256), #functia SHA2 hash-uieste textul
        CONCAT('User ', i),
        CONCAT('user', i, '@example.com'),
        IF(i = 1, 'admin', 'client'));
        SET i = i + 1;
    END WHILE;
END;

