create table Blaga_Filip_Andrei.categorii
(
    id       int auto_increment
        primary key,
    denumire varchar(255) not null,
    constraint denumire
        unique (denumire)
);

grant select on table Blaga_Filip_Andrei.categorii to client_basic@localhost;

