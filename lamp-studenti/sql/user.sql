create table Calorifere.user
(
    id       int auto_increment
        primary key,
    username varchar(255)                              not null,
    parola   varchar(255)                              not null,
    nume     varchar(255)                              null,
    email    varchar(255)                              null,
    rol      enum ('admin', 'client') default 'client' null,
    constraint username
        unique (username)
);

