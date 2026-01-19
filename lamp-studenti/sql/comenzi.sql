create table Calorifere.comenzi
(
    id            int auto_increment
        primary key,
    id_utilizator int                                                                 null,
    data_comenzii datetime                                  default CURRENT_TIMESTAMP null,
    status        enum ('plasata', 'in livrare', 'livrata') default 'plasata'         null,
    constraint comenzi_ibfk_1
        foreign key (id_utilizator) references Calorifere.user (id)
);

create index id_utilizator
    on Calorifere.comenzi (id_utilizator);

