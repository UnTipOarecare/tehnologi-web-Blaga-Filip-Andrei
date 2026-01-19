create table Calorifere.recenzii
(
    id            int auto_increment
        primary key,
    id_produs     int  null,
    id_utilizator int  null,
    rating        int  null,
    comentariu    text null,
    constraint recenzii_ibfk_1
        foreign key (id_produs) references Calorifere.produse (id),
    constraint recenzii_ibfk_2
        foreign key (id_utilizator) references Calorifere.user (id)
);

create index id_produs
    on Calorifere.recenzii (id_produs);

create index id_utilizator
    on Calorifere.recenzii (id_utilizator);

