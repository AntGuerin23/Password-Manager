CREATE USER password_manager with password 'Manager123';

create table "user"
(
	username varchar not null,
	email varchar,
	id serial
		constraint user_pk
			primary key,
	phone_nb varchar,
	password varchar,
	google_auth_key varchar,
	email_mfa boolean,
	salt varchar
);


create table password
(
	user_id integer
		constraint password__fk
			references "user",
	domain varchar,
	password varchar,
	id serial
		constraint password_pk
			primary key,
	username varchar
);


create table connection
(
	id serial
		constraint connection_pk
			primary key,
	user_id integer
		constraint connection_user_id_fk
			references "user",
	ip varchar,
	browser varchar,
	last_login timestamp,
	connection_time timestamp,
	session_id varchar,
	disconnected boolean
);


create table "apiKey"
(
	id serial
		constraint apikey_pk
			primary key,
	user_id integer
		constraint apikey_user__fk
			references "user",
	key varchar not null
);


GRANT SELECT, INSERT, UPDATE, DELETE  on all tables in schema public to password_manager
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO password_manager;

