DROP DATABASE IF EXISTS social_network;
CREATE DATABASE social_network CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use social_network;

create table user(
id int not null primary key auto_increment,
firstname varchar(50) not null,
lastname varchar(50) not null,
email varchar(100) not null,
pass char(60) not null,
image varchar(250) null
)engine=InnoDB;

create unique index ix1 on user(email);


create table post(
id int not null primary key auto_increment,
content text,
user int not null,
date datetime not null default now()
)engine=InnoDB;

create table comment(
id int not null primary key auto_increment,
user int not null,
post int not null,
content text not null,
date datetime not null default now()
)engine=InnoDB;

create table likes(
id int not null primary key auto_increment,
user int not null,
post int not null
)engine=InnoDB;

create table tag (
id int not null primary key auto_increment,
name varchar (100) not null,
post int not null
)engine=InnoDB;


alter table post add FOREIGN KEY (user) REFERENCES user(id);

alter table comment add FOREIGN KEY (user) REFERENCES user(id);
alter table comment add FOREIGN KEY (post) REFERENCES post(id);

alter table likes add FOREIGN KEY (user) REFERENCES user(id);
alter table likes add FOREIGN KEY (post) REFERENCES post(id);

alter table tag add FOREIGN KEY (post) REFERENCES post(id);


insert into user (id,firstname,lastname,email,pass) values
(null,'Pero','Perić','pperic@gmail.com','$2y$10$9.r4TBJ3Yd7UeuofhqdJ1uIDc6Y4kAD2QKQN1PYcuIlK3uliASY9m');

insert into user (firstname,lastname,email,pass) values
('Ana','Ančić','aancic@gmail.com','$2y$10$9.r4TBJ3Yd7UeuofhqdJ1uIDc6Y4kAD2QKQN1PYcuIlK3uliASY9m');


insert into post (content,user) values ('Evo danas pada kiša opet :(',1), ('Jedem jagode.',2);

insert into tag (name,post) values ('Vrijeme',1), ('Hrana',2), ('Prognoza', 1);

