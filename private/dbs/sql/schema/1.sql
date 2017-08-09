-- Framework Management Tables --
DROP TABLE IF EXISTS dbversions;
CREATE TABLE dbversions (
   current INTEGER UNIQUE NOT NULL PRIMARY KEY DEFAULT 0
);

-- Application Tables --
DROP TABLE IF EXISTS customers;
  CREATE TABLE customers (
  customer_id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
  name TEXT NOT NULL UNIQUE,
  address_1 TEXT,
  address_2 TEXT,
  city TEXT,
  state TEXT,
  zip  TEXT,
  phone TEXT,
  fax TEXT,
  website TEXT
);

DROP TABLE IF EXISTS customer_contacts;
CREATE TABLE customer_contacts (
   customer_contact_id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
   customer_id INTEGER NOT NULL,
   first_name TEXT NOT NULL,
   last_name TEXT NOT NULL,
   email TEXT,
   cell_phone TEXT,
   FOREIGN KEY(customer_id) REFERENCES customers(customer_id)
);
CREATE UNIQUE INDEX customer_contacts_unique_idx ON customer_contacts (customer_id ,first_name,last_name);

DROP TABLE IF EXISTS pois;
CREATE TABLE pois (
  poi_id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
  name TEXT UNIQUE NOT NULL,
  notes TEXT,
  address TEXT,
  lat REAL,
  lon REAL
);

DROP TABLE IF EXISTS devices;
CREATE TABLE devices (
  device_id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
  name TEXT UNIQUE NOT NULL,
  notes TEXT
);

DROP TABLE IF EXISTS device_locations;
CREATE TABLE device_locations (
  device_location_id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
  device_id INTEGER NOT NULL,
  logged_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  lat REAL NOT NULL,
  lon REAL NOT NULL,
  FOREIGN KEY (device_id) REFERENCES devices(device_id)
);

DROP TABLE IF EXISTS test_validations;
CREATE TABLE test_validations (
  validation_id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
  notes TEXT UNIQUE,
  nothing TEXT,
  nothing_require TEXT NOT NULL,
  phone TEXT,
  phone_require TEXT NOT NULL,
  email TEXT,
  email_require TEXT NOT NULL,
  url TEXT,
  url_require TEXT NOT NULL,
  number INTEGER,
  number_require INTEGER NOT NULL,
  money REAL,
  money_require REAL NOT NULL,
  date TEXT,
  date_require TEXT NOT NULL,
  time TEXT,
  time_require TEXT NOT NULL,
  intepos INTEGER,
  inteneg INTEGER,
  date_iso TEXT,
  zip TEXT,
  checkbox INTEGER NOT NULL DEFAULT 0,
  CHECK (checkbox IN (0, 1))
);

DROP TABLE IF EXISTS test_checks;
CREATE TABLE test_checks (
  test_check_id INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
  sunday        INTEGER NOT NULL DEFAULT 0,
  monday        INTEGER NOT NULL DEFAULT 0,
  tuesday       INTEGER NOT NULL DEFAULT 0,
  wednesday     INTEGER NOT NULL DEFAULT 0,
  thursday      INTEGER NOT NULL DEFAULT 0,
  friday        INTEGER NOT NULL DEFAULT 0,
  saturday      INTEGER NOT NULL DEFAULT 0,
  CHECK (sunday IN (0, 1)),
  CHECK (monday IN (0, 1)),
  CHECK (tuesday IN (0, 1)),
  CHECK (wednesday IN (0, 1)),
  CHECK (thursday IN (0, 1)),
  CHECK (friday IN (0, 1)),
  CHECK (saturday IN (0, 1))
);
