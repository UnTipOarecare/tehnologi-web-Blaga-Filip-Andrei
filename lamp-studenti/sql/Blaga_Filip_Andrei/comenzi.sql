create table Blaga_Filip_Andrei.comenzi
(
    id            int auto_increment
        primary key,
    id_utilizator int                                                                   null,
    data_comenzii datetime                                  default current_timestamp() null,
    status        enum ('plasata', 'in livrare', 'livrata') default 'plasata'           null,
    constraint comenzi_ibfk_1
        foreign key (id_utilizator) references Blaga_Filip_Andrei.user (id)
);

create index id_utilizator
    on Blaga_Filip_Andrei.comenzi (id_utilizator);

grant insert, select, update on table Blaga_Filip_Andrei.comenzi to angajat_comenzi@localhost;

