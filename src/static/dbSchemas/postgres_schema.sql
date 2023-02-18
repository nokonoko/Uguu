CREATE TABLE files
(
    id           serial PRIMARY KEY,
    hash         text    NOT NULL,
    originalname text    NOT NULL,
    filename     text    NOT NULL,
    size         integer not null,
    date         integer not null,
    ip           text    null
);

CREATE TABLE blacklist
(
    id           serial PRIMARY KEY,
    hash         text    NOT NULL,
    originalname text    NOT NULL,
    time         integer not null,
    ip           text    null
);

CREATE TABLE ratelimit
(
    id     serial PRIMARY KEY,
    iphash text    NOT NULL,
    files  integer not null,
    time   integer not null
);