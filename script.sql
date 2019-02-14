DROP DATABASE IF EXISTS social_network;
CREATE DATABASE social_network CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
use social_network;

create table user(
id int not null primary key auto_increment,
firstname varchar(50) not null,
lastname varchar(50) not null,
email varchar(100) not null,
pass char(60) not null,
image varchar(250) null,
role varchar(50)
)engine=InnoDB;

create unique index ix1 on user(email);


create table post(
id int not null primary key auto_increment,
content text,
user int not null,
date datetime not null default now(),
hidden boolean default false
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
post int not null,
uniquelikes varchar(50)
)engine=InnoDB;

create unique index unique_likes on likes(uniquelikes);

create table tag (
id int not null primary key auto_increment,
name text
)engine=InnoDB;

create table tagpost(
id int not null primary key auto_increment,
post int not null,
tag int not null
)engine=InnoDB;

create table report(
id int not null primary key auto_increment,
userid int not null,
postid int not null,
uniquereport varchar(50)
)engine=InnoDB;

create unique index unique_reports on report(uniquereport);

create table reportcomment(
id int not null primary key auto_increment,
userid int not null,
commentid int not null,
uniquereport varchar(50)
)engine=InnoDB;

create unique index unique_comm_reports on reportcomment(uniquereport);


alter table post add FOREIGN KEY (user) REFERENCES user(id);

alter table comment add FOREIGN KEY (user) REFERENCES user(id);
alter table comment add FOREIGN KEY (post) REFERENCES post(id);

alter table likes add FOREIGN KEY (user) REFERENCES user(id);
alter table likes add FOREIGN KEY (post) REFERENCES post(id);

alter table tagpost add FOREIGN KEY (post) REFERENCES post(id);
alter table tagpost add FOREIGN  KEY (tag) REFERENCES tag(id);

alter table report add FOREIGN KEY (postid) REFERENCES post(id);
alter table report add FOREIGN KEY (userid) REFERENCES user(id);

alter table reportcomment add FOREIGN KEY (commentid) REFERENCES comment(id);
alter table reportcomment add FOREIGN KEY (userid) REFERENCES user(id);



insert into user (id,firstname,lastname,email,pass,role) values
(null,'Pero','Perić','pperic@gmail.com','$2y$10$9.r4TBJ3Yd7UeuofhqdJ1uIDc6Y4kAD2QKQN1PYcuIlK3uliASY9m','admin');




insert into post (content,user) values ('Evo danas pada kiša opet :(',1), ('Jedem jagode.',1);



