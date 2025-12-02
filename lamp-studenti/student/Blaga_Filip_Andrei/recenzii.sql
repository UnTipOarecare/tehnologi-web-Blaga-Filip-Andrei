create table Blaga_Filip_Andrei.recenzii
(
    id            int auto_increment
        primary key,
    id_produs     int  null,
    id_utilizator int  null,
    rating        int  null
        check (`rating` >= 0 and `rating` <= 5),
    comentariu    text null,
    constraint recenzii_ibfk_1
        foreign key (id_produs) references Blaga_Filip_Andrei.produse (id),
    constraint recenzii_ibfk_2
        foreign key (id_utilizator) references Blaga_Filip_Andrei.user (id)
);

create index id_produs
    on Blaga_Filip_Andrei.recenzii (id_produs);

create index id_utilizator
    on Blaga_Filip_Andrei.recenzii (id_utilizator);

grant insert, select on table Blaga_Filip_Andrei.recenzii to user_care_da_doar_reviewuri@localhost;

