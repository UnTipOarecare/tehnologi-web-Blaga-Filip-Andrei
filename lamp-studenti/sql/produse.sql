create table Calorifere.produse
(
    id           int auto_increment
        primary key,
    denumire     varchar(255)   not null,
    descriere    text           null,
    pret         decimal(10, 2) null,
    id_categorie int            null,
    img          varchar(255)   null,
    constraint produse_ibfk_1
        foreign key (id_categorie) references Calorifere.categorii (id)
);

create index id_categorie
    on Calorifere.produse (id_categorie);

