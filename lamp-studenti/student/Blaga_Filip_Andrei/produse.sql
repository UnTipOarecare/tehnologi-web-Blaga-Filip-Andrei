create table Blaga_Filip_Andrei.produse
(
    id           int auto_increment
        primary key,
    denumire     varchar(255)   not null,
    descriere    text           null,
    pret         decimal(10, 2) null,
    id_categorie int            null,
    constraint produse_ibfk_1
        foreign key (id_categorie) references Blaga_Filip_Andrei.categorii (id)
);

create index id_categorie
    on Blaga_Filip_Andrei.produse (id_categorie);

grant delete, insert, select, update on table Blaga_Filip_Andrei.produse to angajat_care_se_ocupa_de_stoc@localhost;

grant select on table Blaga_Filip_Andrei.produse to client_basic@localhost;

