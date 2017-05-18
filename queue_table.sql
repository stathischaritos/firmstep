CREATE TABLE IF NOT EXISTS queue (
   `type` text NOT NULL,
   `firstName` text NOT NULL,
   `lastName` text NOT NULL,
   `organisation` text,
   `service` text NOT NULL,
   `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP
);
