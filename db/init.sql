CREATE DATABASE IF NOT EXISTS modgame;
CREATE USER IF NOT EXISTS 'user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON *.* TO 'user'@'%';
FLUSH PRIVILEGES;


--
SET NAMES 'utf8mb4';

--
-- Set default database
--
USE modgame;

--
-- Drop procedure `search_table_data`
--
DROP PROCEDURE IF EXISTS search_table_data;

--
-- Drop table `admin`
--
DROP TABLE IF EXISTS admin;

--
-- Drop table `users`
--
DROP TABLE IF EXISTS users;

--
-- Drop procedure `calculate_rating`
--
DROP PROCEDURE IF EXISTS calculate_rating;

--
-- Drop procedure `get_game_details`
--
DROP PROCEDURE IF EXISTS get_game_details;

--
-- Drop table `developers`
--
DROP TABLE IF EXISTS developers;

--
-- Drop table `artist`
--
DROP TABLE IF EXISTS artist;

--
-- Drop table `programmer`
--
DROP TABLE IF EXISTS programmer;

--
-- Drop procedure `update_mod_file_size_all`
--
DROP PROCEDURE IF EXISTS update_mod_file_size_all;

--
-- Drop function `calculate_total_mod_file_size`
--
DROP FUNCTION IF EXISTS calculate_total_mod_file_size;

--
-- Drop function `get_asset_count`
--
DROP FUNCTION IF EXISTS get_asset_count;

--
-- Drop table `asset`
--
DROP TABLE IF EXISTS asset;

--
-- Drop table `asset_type`
--
DROP TABLE IF EXISTS asset_type;

--
-- Drop procedure `update_game_release_date`
--
DROP PROCEDURE IF EXISTS update_game_release_date;

--
-- Drop function `get_latest_mod`
--
DROP FUNCTION IF EXISTS get_latest_mod;

--
-- Drop table `game_mod`
--
DROP TABLE IF EXISTS game_mod;

--
-- Drop table `mod_type`
--
DROP TABLE IF EXISTS mod_type;

--
-- Drop table `game`
--
DROP TABLE IF EXISTS game;

--
-- Drop table `game_genre`
--
DROP TABLE IF EXISTS game_genre;

--
-- Set default database
--
USE modgame;

--
-- Create table `game_genre`
--
CREATE TABLE game_genre (
  id_genre int NOT NULL AUTO_INCREMENT,
  genre_name varchar(50) NOT NULL,
  game_count int NOT NULL,
  PRIMARY KEY (id_genre)
)
ENGINE = INNODB,
AUTO_INCREMENT = 15,
AVG_ROW_LENGTH = 2340,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create table `game`
--
CREATE TABLE game (
  id_game int NOT NULL AUTO_INCREMENT,
  game_name varchar(50) NOT NULL,
  id_genre int NOT NULL,
  release_date date NOT NULL,
  mod_count int NOT NULL,
  PRIMARY KEY (id_game)
)
ENGINE = INNODB,
AUTO_INCREMENT = 12,
AVG_ROW_LENGTH = 1638,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create foreign key
--
ALTER TABLE game
ADD CONSTRAINT game_ibfk_1 FOREIGN KEY (id_genre)
REFERENCES game_genre (id_genre) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Create table `mod_type`
--
CREATE TABLE mod_type (
  id_mod_type int NOT NULL AUTO_INCREMENT,
  mod_type_name varchar(50) NOT NULL,
  mod_count int NOT NULL,
  PRIMARY KEY (id_mod_type)
)
ENGINE = INNODB,
AUTO_INCREMENT = 7,
AVG_ROW_LENGTH = 2730,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create table `game_mod`
--
CREATE TABLE game_mod (
  id_game_mod int NOT NULL AUTO_INCREMENT,
  id_game int NOT NULL,
  id_mod_type int NOT NULL,
  mod_name varchar(50) NOT NULL,
  release_date date NOT NULL,
  download_count int NOT NULL,
  file_size_MB float NOT NULL,
  description varchar(50) NOT NULL,
  rating int NOT NULL,
  PRIMARY KEY (id_game_mod)
)
ENGINE = INNODB,
AUTO_INCREMENT = 9,
AVG_ROW_LENGTH = 2730,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create foreign key
--
ALTER TABLE game_mod
ADD CONSTRAINT game_mod_ibfk_1 FOREIGN KEY (id_game)
REFERENCES game (id_game) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Create foreign key
--
ALTER TABLE game_mod
ADD CONSTRAINT game_mod_ibfk_2 FOREIGN KEY (id_mod_type)
REFERENCES mod_type (id_mod_type) ON DELETE CASCADE ON UPDATE CASCADE;

DELIMITER $$

--
-- Create function `get_latest_mod`
--
CREATE
DEFINER = 'root'@'%'
FUNCTION get_latest_mod (gameID int)
RETURNS varchar(50) CHARSET utf8mb4 COLLATE utf8mb4_0900_ai_ci
DETERMINISTIC
BEGIN
  DECLARE latestModName varchar(50);

  SELECT
    mod_name INTO latestModName
  FROM game_mod
  WHERE id_game = gameID
  ORDER BY release_date DESC
  LIMIT 1;
  RETURN COALESCE(latestModName, 'No Mods Available');
END
$$

--
-- Create procedure `update_game_release_date`
--
CREATE
DEFINER = 'root'@'%'
PROCEDURE update_game_release_date (IN gameID int, IN newReleaseDate date)
BEGIN
  -- Update game release date
  UPDATE game
  SET release_date = newReleaseDate
  WHERE id_game = gameID;

  -- Update mods release dates
  UPDATE game_mod
  SET release_date = newReleaseDate
  WHERE id_game = gameID;
END
$$

DELIMITER ;

--
-- Create table `asset_type`
--
CREATE TABLE asset_type (
  id_asset_type int NOT NULL AUTO_INCREMENT,
  asset_type_name varchar(50) NOT NULL,
  asset_count int NOT NULL,
  PRIMARY KEY (id_asset_type)
)
ENGINE = INNODB,
AUTO_INCREMENT = 8,
AVG_ROW_LENGTH = 2340,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create table `asset`
--
CREATE TABLE asset (
  id_asset int NOT NULL AUTO_INCREMENT,
  id_game_mod int NOT NULL,
  id_game int NOT NULL,
  id_asset_type int NOT NULL,
  asset_name varchar(50) NOT NULL,
  file_size_MB float NOT NULL,
  creation_date date NOT NULL,
  description varchar(50) NOT NULL,
  PRIMARY KEY (id_asset)
)
ENGINE = INNODB,
AUTO_INCREMENT = 9,
AVG_ROW_LENGTH = 2730,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create foreign key
--
ALTER TABLE asset
ADD CONSTRAINT asset_ibfk_1 FOREIGN KEY (id_game_mod)
REFERENCES game_mod (id_game_mod) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Create foreign key
--
ALTER TABLE asset
ADD CONSTRAINT asset_ibfk_2 FOREIGN KEY (id_game)
REFERENCES game (id_game) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Create foreign key
--
ALTER TABLE asset
ADD CONSTRAINT asset_ibfk_3 FOREIGN KEY (id_asset_type)
REFERENCES asset_type (id_asset_type) ON DELETE CASCADE ON UPDATE CASCADE;

DELIMITER $$

--
-- Create function `get_asset_count`
--
CREATE
DEFINER = 'root'@'%'
FUNCTION get_asset_count (gameModID int)
RETURNS int(11)
DETERMINISTIC
BEGIN
  DECLARE assetCount int;

  SELECT
    COUNT(*) INTO assetCount
  FROM asset
  WHERE id_game_mod = gameModID;

  RETURN COALESCE(assetCount, 0);
END
$$

--
-- Create function `calculate_total_mod_file_size`
--
CREATE
DEFINER = 'root'@'%'
FUNCTION calculate_total_mod_file_size (modID int)
RETURNS float
DETERMINISTIC
BEGIN
  DECLARE totalFileSize float;

  SELECT
    SUM(file_size_MB) INTO totalFileSize
  FROM asset
  WHERE id_game_mod = modID;

  RETURN COALESCE(totalFileSize, 0);
END
$$

--
-- Create procedure `update_mod_file_size_all`
--
CREATE
DEFINER = 'root'@'%'
PROCEDURE update_mod_file_size_all ()
BEGIN
  DECLARE done boolean DEFAULT FALSE;
  DECLARE modIdToUpdate int;
  -- Declare variables to store the calculated total file size
  DECLARE totalModFileSize float;
  -- Cursor to iterate over each row in game_mod
  DECLARE cur CURSOR FOR
  SELECT
    id_game_mod
  FROM game_mod;



  -- Declare continue handler to exit the loop
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  -- Open the cursor
  OPEN cur;

  -- Start fetching rows
  FETCH cur INTO modIdToUpdate;

  -- Loop to update file_size_MB for each game_mod entry
  REPEAT
    -- Check if the cursor has fetched a row
    IF NOT done THEN
      -- Call the function to calculate the total file size for the current game_mod
      SET totalModFileSize = calculate_total_mod_file_size(modIdToUpdate);

      -- Update the file_size_MB for the current game_mod entry
      UPDATE game_mod
      SET file_size_MB = totalModFileSize
      WHERE id_game_mod = modIdToUpdate;
    END IF;

    -- Fetch the next row
    FETCH cur INTO modIdToUpdate;

  UNTIL done END REPEAT;

  -- Close the cursor
  CLOSE cur;
END
$$

DELIMITER ;

--
-- Create table `programmer`
--
CREATE TABLE programmer (
  id_programmer int NOT NULL AUTO_INCREMENT,
  programmer_name varchar(50) NOT NULL,
  work_count int NOT NULL,
  rating int NOT NULL,
  PRIMARY KEY (id_programmer)
)
ENGINE = INNODB,
AUTO_INCREMENT = 8,
AVG_ROW_LENGTH = 2340,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create table `artist`
--
CREATE TABLE artist (
  id_artist int NOT NULL AUTO_INCREMENT,
  artist_name varchar(50) NOT NULL,
  work_count int NOT NULL,
  rating int NOT NULL,
  PRIMARY KEY (id_artist)
)
ENGINE = INNODB,
AUTO_INCREMENT = 8,
AVG_ROW_LENGTH = 2340,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create table `developers`
--
CREATE TABLE developers (
  id_game_mod int NOT NULL,
  id_game int NOT NULL,
  id_programmer int NOT NULL,
  id_artist int NOT NULL
)
ENGINE = INNODB,
AVG_ROW_LENGTH = 2730,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create foreign key
--
ALTER TABLE developers
ADD CONSTRAINT developers_ibfk_1 FOREIGN KEY (id_programmer)
REFERENCES programmer (id_programmer) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Create foreign key
--
ALTER TABLE developers
ADD CONSTRAINT developers_ibfk_2 FOREIGN KEY (id_game)
REFERENCES game (id_game) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Create foreign key
--
ALTER TABLE developers
ADD CONSTRAINT developers_ibfk_3 FOREIGN KEY (id_game_mod)
REFERENCES game_mod (id_game_mod) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Create foreign key
--
ALTER TABLE developers
ADD CONSTRAINT developers_ibfk_4 FOREIGN KEY (id_artist)
REFERENCES artist (id_artist) ON DELETE CASCADE ON UPDATE CASCADE;

DELIMITER $$

--
-- Create procedure `get_game_details`
--
CREATE
DEFINER = 'root'@'%'
PROCEDURE get_game_details (IN gameID int)
BEGIN
  DECLARE gameName varchar(50);
  DECLARE releaseDate date;
  DECLARE modCount int;

  -- Retrieve game information
  SELECT
    game_name,
    release_date,
    mod_count INTO gameName, releaseDate, modCount
  FROM game
  WHERE id_game = gameID;

  -- Display game information
  SELECT
    CONCAT('Game Name: ', gameName) AS 'Game Information',
    CONCAT('Release Date: ', releaseDate) AS 'Release Date',
    CONCAT('Mod Count: ', modCount) AS 'Mod Count';

  -- Display mods and developers information
  SELECT
    CONCAT('Mod Name: ', gm.mod_name) AS 'Mod Information',
    CONCAT('Release Date: ', gm.release_date) AS 'Mod Release Date',
    CONCAT('Download Count: ', gm.download_count) AS 'Download Count',
    CONCAT('Developer: ', p.programmer_name, ' (Programmer)') AS 'Developer'
  FROM game_mod gm
    JOIN developers d
      ON gm.id_game_mod = d.id_game_mod
    JOIN programmer p
      ON d.id_programmer = p.id_programmer
  WHERE gm.id_game = gameID;

  SELECT
    CONCAT('Mod Name: ', gm.mod_name) AS 'Mod Information',
    CONCAT('Release Date: ', gm.release_date) AS 'Mod Release Date',
    CONCAT('Download Count: ', gm.download_count) AS 'Download Count',
    CONCAT('Developer: ', a.artist_name, ' (Artist)') AS 'Developer'
  FROM game_mod gm
    JOIN developers d
      ON gm.id_game_mod = d.id_game_mod
    JOIN artist a
      ON d.id_artist = a.id_artist
  WHERE gm.id_game = gameID;
END
$$

--
-- Create procedure `calculate_rating`
--
CREATE
DEFINER = 'root'@'%'
PROCEDURE calculate_rating (IN artistID int, IN programmerID int)
BEGIN
  -- Declare variables for storing the calculated ratings
  DECLARE artistRating decimal(5, 2);
  DECLARE programmerRating decimal(5, 2);

  -- Calculate average rating for the artist
  SELECT
    AVG(gm.rating) INTO artistRating
  FROM game_mod gm
    JOIN developers d
      ON gm.id_game_mod = d.id_game_mod
  WHERE d.id_artist = artistID;

  -- Calculate average rating for the programmer
  SELECT
    AVG(gm.rating) INTO programmerRating
  FROM game_mod gm
    JOIN developers d
      ON gm.id_game_mod = d.id_game_mod
  WHERE d.id_programmer = programmerID;

  -- Update the artist and programmer tables with the calculated ratings
  UPDATE artist
  SET rating = artistRating
  WHERE id_artist = artistID;
  UPDATE programmer
  SET rating = programmerRating
  WHERE id_programmer = programmerID;
END
$$

DELIMITER ;

--
-- Create table `users`
--
CREATE TABLE users (
  id int NOT NULL AUTO_INCREMENT,
  username varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

--
-- Create table `admin`
--
CREATE TABLE admin (
  id int NOT NULL AUTO_INCREMENT,
  login varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB,
CHARACTER SET utf8mb4,
COLLATE utf8mb4_0900_ai_ci,
ROW_FORMAT = DYNAMIC;

DELIMITER $$

--
-- Create procedure `search_table_data`
--
CREATE
DEFINER = 'root'@'%'
PROCEDURE search_table_data (IN table_name varchar(255), IN search_term varchar(255))
BEGIN
  SET @query = CONCAT('SELECT * FROM ', table_name, ' WHERE CONCAT(', column_names(table_name), ') LIKE ''%', search_term, '%''');
  PREPARE stmt FROM @query;
  EXECUTE stmt;
  DEALLOCATE PREPARE stmt;
END
$$

DELIMITER ;

-- 
-- Dumping data for table game_genre
--
INSERT INTO game_genre VALUES
(1, 'Action', 4),
(2, 'Adventure', 1),
(3, 'Simulation', 1),
(4, 'Strategy', 1),
(5, 'RPG', 2),
(6, 'Sports', 1),
(7, 'Puzzle', 1);

-- 
-- Dumping data for table mod_type
--
INSERT INTO mod_type VALUES
(1, 'Graphics', 2),
(2, 'Gameplay', 1),
(3, 'Sound', 2),
(4, 'Characters', 1),
(5, 'Utilities', 1),
(6, 'Other', 1);

-- 
-- Dumping data for table game
--
INSERT INTO game VALUES
(1, 'The Witcher 3: Wild Hunt', 5, '2015-05-19', 1),
(2, 'Minecraft', 6, '2011-11-18', 1),
(3, 'Stardew Valley', 3, '2016-02-26', 1),
(4, 'Counter-Strike: Global Offensive', 1, '2012-08-21', 1),
(5, 'The Legend of Zelda: Breath of the Wild', 2, '2017-04-03', 2),
(6, 'Fallout 4', 5, '2015-11-10', 1),
(7, 'Among Us', 4, '2018-11-16', 1),
(8, 'Fortnite', 1, '2017-07-25', 0),
(9, 'Overwatch', 1, '2016-05-24', 0),
(10, 'League of Legends', 1, '2009-10-27', 0),
(11, 'Professor Layton', 7, '2007-04-21', 0);

-- 
-- Dumping data for table programmer
--
INSERT INTO programmer VALUES
(1, 'John Programmer', 3, 4),
(2, 'Jane Coder', 2, 4),
(3, 'Bob Developer', 2, 5),
(4, 'Alice Engineer', 1, 3);

-- 
-- Dumping data for table artist
--
INSERT INTO artist VALUES
(1, 'Вася', 2, 4),
(2, 'Creative Kate', 2, 5),
(3, 'Visual Vivian', 2, 4),
(4, 'Designing Dave', 2, 4);

-- 
-- Dumping data for table game_mod
--
INSERT INTO game_mod VALUES
(1, 1, 1, 'HD Graphics Overhaul', '2023-01-05', 1000, 12.7, 'Enhances game graphics to high definition', 5),
(2, 2, 2, 'Adventure Expansion', '2023-02-20', 800, 8.5, 'Introduces new quests and adventures', 4),
(3, 5, 3, 'Immersive Sound Pack', '2017-04-03', 1200, 12.8, 'Provides a more immersive audio experience', 4),
(4, 4, 4, 'NPC Diversity Pack', '2023-05-15', 900, 15, 'Adds diverse characters to enhance gameplay', 3),
(5, 5, 5, 'Utility Suite', '2017-04-03', 1500, 20, 'Offers a collection of useful in-game utilities', 5),
(6, 6, 6, 'Community Enhancement Pack', '2023-08-02', 700, 6.5, 'Various enhancements contributed by the community', 4),
(7, 3, 1, 'Realistic Environment Mod', '2023-09-18', 1100, 14.5, 'Creates a more realistic in-game environment', 5),
(8, 7, 3, 'Epic Soundtrack Expansion', '2023-11-01', 800, 0, 'Expands the game soundtrack for an epic experience', 2);

-- 
-- Dumping data for table asset_type
--
INSERT INTO asset_type VALUES
(1, 'Texture', 3),
(2, 'Model', 1),
(3, 'Audio', 1),
(4, 'Script', 1),
(5, 'Document', 1),
(6, 'Other', 1);

-- 
-- Dumping data for table users
--
INSERT INTO users VALUES
(1, 'user', '12345user');

-- 
-- Dumping data for table developers
--
INSERT INTO developers VALUES
(1, 1, 1, 1),
(2, 2, 2, 2),
(3, 5, 3, 3),
(4, 4, 4, 4),
(5, 5, 1, 2),
(6, 6, 2, 3),
(7, 3, 3, 4),
(8, 7, 1, 1);

-- 
-- Dumping data for table asset
--
INSERT INTO asset VALUES
(1, 1, 1, 1, 'High-Res Textures', 10.2, '2023-01-10', 'Enhanced textures for the graphics mod'),
(2, 2, 2, 2, 'New Gameplay Scripts', 8.5, '2023-02-25', 'Additional scripts for the gameplay mod'),
(3, 3, 5, 3, 'Improved Soundtrack', 12.8, '2023-04-05', 'Enhanced audio tracks for the sound mod'),
(4, 4, 4, 4, 'New Characters Models', 15, '2023-05-18', 'Additional character models for the characters mod'),
(5, 5, 5, 5, 'Utility Scripts', 20, '2023-06-15', 'Useful scripts for the utilities mod'),
(6, 6, 6, 6, 'Miscellaneous Enhancements', 6.5, '2023-08-05', 'Various other enhancements for the other mod'),
(7, 7, 3, 1, 'Enhanced Graphics Textures', 14.5, '2023-09-20', 'Further enhanced textures for the graphics mod'),
(8, 1, 1, 1, 'new texture', 2.5, '2023-11-26', 'New texture');

-- 
-- Dumping data for table admin
--
INSERT INTO admin VALUES
(1, 'admin', '12345');

--
-- Set default database
--
USE modgame;

--
-- Drop trigger `decrease_asset_count`
--
DROP TRIGGER IF EXISTS decrease_asset_count;

--
-- Drop trigger `decrease_game_count`
--
DROP TRIGGER IF EXISTS decrease_game_count;

--
-- Drop trigger `increase_game_count`
--
DROP TRIGGER IF EXISTS increase_game_count;

--
-- Drop trigger `update_artist_programmer_rating`
--
DROP TRIGGER IF EXISTS update_artist_programmer_rating;

--
-- Drop trigger `decrease_art_work_count`
--
DROP TRIGGER IF EXISTS decrease_art_work_count;

--
-- Drop trigger `decrease_prog_work_count`
--
DROP TRIGGER IF EXISTS decrease_prog_work_count;

--
-- Drop trigger `decrease_game_mod_count`
--
DROP TRIGGER IF EXISTS decrease_game_mod_count;

--
-- Drop trigger `decrease_mod_type_count`
--
DROP TRIGGER IF EXISTS decrease_mod_type_count;

--
-- Drop trigger `increase_prog_work_count`
--
DROP TRIGGER IF EXISTS increase_prog_work_count;

--
-- Drop trigger `increase_art_work_count`
--
DROP TRIGGER IF EXISTS increase_art_work_count;

--
-- Drop trigger `trigger1`
--
DROP TRIGGER IF EXISTS trigger1;

--
-- Drop trigger `update_mod_file_size_after_insert`
--
DROP TRIGGER IF EXISTS update_mod_file_size_after_insert;

--
-- Drop trigger `increase_asset_count`
--
DROP TRIGGER IF EXISTS increase_asset_count;

--
-- Drop trigger `update_artist_programmer_rating_insert`
--
DROP TRIGGER IF EXISTS update_artist_programmer_rating_insert;

--
-- Drop trigger `increase_game_mod_count`
--
DROP TRIGGER IF EXISTS increase_game_mod_count;

--
-- Drop trigger `increase_game_mod_type_count`
--
DROP TRIGGER IF EXISTS increase_game_mod_type_count;

--
-- Set default database
--
USE modgame;

DELIMITER $$

--
-- Create trigger `increase_game_mod_type_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER increase_game_mod_type_count
AFTER INSERT
ON game_mod
FOR EACH ROW
BEGIN
  UPDATE mod_type
  SET mod_count = mod_count + 1
  WHERE id_mod_type = NEW.id_mod_type;
END
$$

--
-- Create trigger `increase_game_mod_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER increase_game_mod_count
AFTER INSERT
ON game_mod
FOR EACH ROW
FOLLOWS increase_game_mod_type_count
BEGIN
  UPDATE game
  SET mod_count = mod_count + 1
  WHERE id_game = NEW.id_game;
END
$$

--
-- Create trigger `update_artist_programmer_rating_insert`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER update_artist_programmer_rating_insert
AFTER INSERT
ON game_mod
FOR EACH ROW
FOLLOWS increase_game_mod_count
BEGIN
  DECLARE artistID int;
  DECLARE programmerID int;

  -- Get the artist and programmer IDs associated with the new mod
  SELECT
    d.id_artist,
    d.id_programmer INTO artistID, programmerID
  FROM developers d
  WHERE d.id_game_mod = NEW.id_game_mod;

  -- Update the artist rating
  UPDATE artist
  SET rating = (SELECT
      AVG(gm.rating)
    FROM game_mod gm
    WHERE gm.id_game_mod IN (SELECT
        id_game_mod
      FROM developers
      WHERE id_artist = artistID))
  WHERE id_artist = artistID;

  -- Update the programmer rating
  UPDATE programmer
  SET rating = (SELECT
      AVG(gm.rating)
    FROM game_mod gm
    WHERE gm.id_game_mod IN (SELECT
        id_game_mod
      FROM developers
      WHERE id_programmer = programmerID))
  WHERE id_programmer = programmerID;
END
$$

--
-- Create trigger `increase_asset_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER increase_asset_count
AFTER INSERT
ON asset
FOR EACH ROW
BEGIN
  UPDATE asset_type
  SET asset_count = asset_count + 1
  WHERE id_asset_type = NEW.id_asset_type;
END
$$

--
-- Create trigger `update_mod_file_size_after_insert`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER update_mod_file_size_after_insert
AFTER INSERT
ON asset
FOR EACH ROW
FOLLOWS increase_asset_count
BEGIN
  DECLARE modIDToUpdate int;
  DECLARE totalModFileSize float;

  -- Get the id_game_mod associated with the new asset
  SELECT
    id_game_mod INTO modIDToUpdate
  FROM game_mod
  WHERE id_game_mod = NEW.id_game_mod;

  -- Check if the associated game_mod exists
  IF modIDToUpdate IS NOT NULL THEN
    -- Call the function to calculate the total file size for the current game_mod
    SET totalModFileSize = calculate_total_mod_file_size(modIDToUpdate);

    -- Update the file_size_MB for the current game_mod entry
    UPDATE game_mod
    SET file_size_MB = totalModFileSize
    WHERE id_game_mod = modIDToUpdate;
  END IF;
END
$$

--
-- Create trigger `trigger1`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER trigger1
AFTER INSERT
ON asset
FOR EACH ROW
FOLLOWS update_mod_file_size_after_insert
BEGIN
END
$$

--
-- Create trigger `increase_art_work_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER increase_art_work_count
AFTER INSERT
ON developers
FOR EACH ROW
BEGIN
  UPDATE artist
  SET work_count = work_count + 1
  WHERE id_artist = NEW.id_artist;
END
$$

--
-- Create trigger `increase_prog_work_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER increase_prog_work_count
AFTER INSERT
ON developers
FOR EACH ROW
FOLLOWS increase_art_work_count
BEGIN
  UPDATE programmer
  SET work_count = work_count + 1
  WHERE id_programmer = NEW.id_programmer;
END
$$

--
-- Create trigger `decrease_mod_type_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER decrease_mod_type_count
AFTER DELETE
ON game_mod
FOR EACH ROW
BEGIN
  DECLARE modTypeID int;

  -- Get the id_mod_type of the deleted row
  SET modTypeID = OLD.id_mod_type;

  -- Decrease mod_count in mod_type by 1
  UPDATE mod_type
  SET mod_count = mod_count - 1
  WHERE id_mod_type = modTypeID;
END
$$

--
-- Create trigger `decrease_game_mod_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER decrease_game_mod_count
AFTER DELETE
ON game_mod
FOR EACH ROW
FOLLOWS decrease_mod_type_count
BEGIN
  DECLARE gameID int;

  -- Get the id_game of the deleted row
  SET gameID = OLD.id_game;

  -- Decrease mod_count in game by 1
  UPDATE game
  SET mod_count = mod_count - 1
  WHERE id_game = gameID;
END
$$

--
-- Create trigger `decrease_prog_work_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER decrease_prog_work_count
AFTER DELETE
ON developers
FOR EACH ROW
BEGIN
  DECLARE programmerID int;

  -- Get the id_programmer of the deleted row
  SET programmerID = OLD.id_programmer;

  -- Decrease work_count in programmer by 1
  UPDATE programmer
  SET work_count = work_count - 1
  WHERE id_programmer = programmerID;
END
$$

--
-- Create trigger `decrease_art_work_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER decrease_art_work_count
AFTER DELETE
ON developers
FOR EACH ROW
FOLLOWS decrease_prog_work_count
BEGIN
  DECLARE artistID int;

  -- Get the id_artist of the deleted row
  SET artistID = OLD.id_artist;

  -- Decrease work_count in artist by 1
  UPDATE artist
  SET work_count = work_count - 1
  WHERE id_artist = artistID;
END
$$

--
-- Create trigger `update_artist_programmer_rating`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER update_artist_programmer_rating
AFTER UPDATE
ON game_mod
FOR EACH ROW
BEGIN
  DECLARE artistID int;
  DECLARE programmerID int;

  -- Get the artist and programmer IDs associated with the updated mod
  SELECT
    d.id_artist,
    d.id_programmer INTO artistID, programmerID
  FROM developers d
  WHERE d.id_game_mod = NEW.id_game_mod;

  -- Update the artist rating
  UPDATE artist
  SET rating = (SELECT
      AVG(gm.rating)
    FROM game_mod gm
    WHERE gm.id_game_mod IN (SELECT
        id_game_mod
      FROM developers
      WHERE id_artist = artistID))
  WHERE id_artist = artistID;

  -- Update the programmer rating
  UPDATE programmer
  SET rating = (SELECT
      AVG(gm.rating)
    FROM game_mod gm
    WHERE gm.id_game_mod IN (SELECT
        id_game_mod
      FROM developers
      WHERE id_programmer = programmerID))
  WHERE id_programmer = programmerID;
END
$$

--
-- Create trigger `increase_game_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER increase_game_count
AFTER INSERT
ON game
FOR EACH ROW
BEGIN
  UPDATE game_genre
  SET game_count = game_count + 1
  WHERE id_genre = NEW.id_genre;
END
$$

--
-- Create trigger `decrease_game_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER decrease_game_count
AFTER DELETE
ON game
FOR EACH ROW
BEGIN
  DECLARE genreID int;

  -- Get the id_genre of the deleted row
  SET genreID = OLD.id_genre;

  -- Decrease game_count in game_genre by 1
  UPDATE game_genre
  SET game_count = game_count - 1
  WHERE id_genre = genreID;
END
$$

--
-- Create trigger `decrease_asset_count`
--
CREATE
DEFINER = 'root'@'%'
TRIGGER decrease_asset_count
AFTER DELETE
ON asset
FOR EACH ROW
BEGIN
  DECLARE assetTypeID int;

  -- Get the id_asset_type of the deleted row
  SET assetTypeID = OLD.id_asset_type;

  -- Decrease asset_count in asset_type by 1
  UPDATE asset_type
  SET asset_count = asset_count - 1
  WHERE id_asset_type = assetTypeID;
END
$$

DELIMITER ;

