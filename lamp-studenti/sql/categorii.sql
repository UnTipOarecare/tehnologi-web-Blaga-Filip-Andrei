create table Calorifere.categorii
(
    id       int auto_increment
        primary key,
    denumire varchar(255) not null,
    constraint denumire
        unique (denumire)
);

