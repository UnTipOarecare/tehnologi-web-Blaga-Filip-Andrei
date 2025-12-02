create table Blaga_Filip_Andrei.detalii_comenzi
(
    id         int auto_increment
        primary key,
    id_comanda int null,
    id_produs  int null,
    cantitate  int null,
    constraint detalii_comenzi_ibfk_1
        foreign key (id_comanda) references Blaga_Filip_Andrei.comenzi (id),
    constraint detalii_comenzi_ibfk_2
        foreign key (id_produs) references Blaga_Filip_Andrei.produse (id)
);

create index id_comanda
    on Blaga_Filip_Andrei.detalii_comenzi (id_comanda);

create index id_produs
    on Blaga_Filip_Andrei.detalii_comenzi (id_produs);

grant insert, select, update on table Blaga_Filip_Andrei.detalii_comenzi to angajat_comenzi@localhost;

