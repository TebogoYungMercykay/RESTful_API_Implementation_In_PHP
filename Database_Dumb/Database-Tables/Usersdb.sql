BEGIN;

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(129) NOT NULL,
  `API_key` varchar(32) NOT NULL,
  `salt` int(12) NOT NULL,
  `account` varchar(10) NOT NULL,
  `logged_in` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `api_key` (`API_key`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

LOCK TABLES `users` WRITE;

INSERT INTO users VALUES(1, 'Test', 'User', 'testuser@tuks.co.za', 'MjAyNjM2ODk3Njc0N2NmYWY3NzI4YjgxYzM0ODk4NTcwMGJkMGNmMTJj', 'a9198b68355f78830054c31a39916b7f', 2026368976, 'default', 0);
INSERT INTO users VALUES(2, 'John', 'Doe', 'johndoe3@gmail.com', 'MjEzNjY5MjE4OGYwZjAwMjY3ZWViZmYwNDNkNDBhYWIwMmNlOGQwNjEw', 'K9yW8cGnE3qTfR7xV2sZ6bN1mJ4jL5p', 2136692188, 'default', 0);

UNLOCK TABLES;
COMMIT