create table Calorifere.detalii_comenzi
(
    id         int auto_increment
        primary key,
    id_comanda int null,
    id_produs  int null,
    cantitate  int null,
    constraint detalii_comenzi_ibfk_1
        foreign key (id_comanda) references Calorifere.comenzi (id),
    constraint detalii_comenzi_ibfk_2
        foreign key (id_produs) references Calorifere.produse (id)
);

create index id_comanda
    on Calorifere.detalii_comenzi (id_comanda);

create index id_produs
    on Calorifere.detalii_comenzi (id_produs);

