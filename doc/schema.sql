DROP TABLE IF EXISTS `family_tree`;
CREATE TABLE `family_tree` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- Table structure for table `gender`

DROP TABLE IF EXISTS `gender`;
CREATE TABLE `gender` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);

-- Table structure for table `people_tags`

DROP TABLE IF EXISTS `people_tags`;
CREATE TABLE `people_tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) NOT NULL,
  `person_id` int DEFAULT NULL,
  `family_tree_id` int NOT NULL,
  PRIMARY KEY (`tag_id`)
);

-- Table structure for table `person`

DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gedcom_id` VARCHAR(20) UNIQUE,  -- This stores the GEDCOM tag like @F1@
  `family_tree_id` int NOT NULL,
  `title` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `alias1` varchar(150) DEFAULT NULL,
  `alias2` varchar(150) DEFAULT NULL,
  `alias3` varchar(150) DEFAULT NULL,
  `source` varchar(150) DEFAULT NULL,
  `preferred_name` varchar(100) DEFAULT NULL,
  `native_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `place_of_death` varchar(255) DEFAULT NULL,
  `gender_id` int DEFAULT NULL,
  `optional_fields` json DEFAULT NULL,
  `body` text ,
  `alive` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `passed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE if exists notes;
-- Table to store additional notes about individuals, families, or events (optional GEDCOM notes)
CREATE TABLE notes (
    note_id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT,  -- Nullable, if this note is for an individual
    family_id INT,      -- Nullable, if this note is for a family
    event_id INT,       -- Nullable, if this note is for an event
    note_text TEXT,
    data JSON,  -- For any additional optional data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table to store events related to individuals or families (events in GEDCOM)
DROP TABLE if exist events;
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50),  -- E.g., 'BIRTH', 'DEATH', 'MARRIAGE', 'DIVORCE'
    event_date DATE,
    event_place_id INT,
    individual_id INT,  -- Nullable, if this is an individual event
    family_id INT,      -- Nullable, if this is a family event
    description TEXT,
    data JSON,  -- For any additional optional data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table to store sources (optional GEDCOM SOUR)
CREATE TABLE sources (
    source_id INT AUTO_INCREMENT PRIMARY KEY,
    source_title VARCHAR(255),
    source_text TEXT,
    data JSON,  -- For any additional optional data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table to link individuals/families/events to sources (GEDCOM SOUR link)
CREATE TABLE source_links (
    source_link_id INT AUTO_INCREMENT PRIMARY KEY,
    individual_id INT,  -- Nullable, if this source is for an individual
    family_id INT,      -- Nullable, if this source is for a family
    event_id INT,       -- Nullable, if this source is for an event
    source_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table structure for table `person_relationship`

DROP TABLE IF EXISTS `person_relationship`;
CREATE TABLE `person_relationship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `person_id1` int NOT NULL,
  `person_id2` int NOT NULL,
  `relationship_type_id` int NOT NULL,
  `relation_start` date DEFAULT NULL,
  `code` VARCHAR(32) DEFAULT NULL,
  `relation_end` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `optional_fields` json DEFAULT NULL,
  PRIMARY KEY (`id`)
);

-- Table to store families

DROP TABLE IF EXISTS `families`;
CREATE TABLE families (
    family_id INT AUTO_INCREMENT PRIMARY KEY,
    gedcom_id VARCHAR(20) UNIQUE,  -- This stores the GEDCOM tag like @F1@
    husband_id INT,
    wife_id INT,
    marriage_date DATE,
    divorce_date DATE,
    marriage_place_id INT,
    divorce_place_id INT,
    data JSON,  -- For any additional optional data
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table to store parent-child relationships
DROP TABLE IF EXISTS families_relationships
CREATE TABLE family_relationships (
    `family_id` INT,
    `child_id` INT,
    `code` VARCHAR(32),
    `data` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table structure for table `relationship_type`

DROP TABLE IF EXISTS `relationship_type`;
CREATE TABLE `relationship_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `family_tree_id` int NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
);
INSERT INTO `relationship_type` (`id`, `family_tree_id`, `code`, `description`) VALUES
(1,1,'SIBL','Sibling'),
(2,1,'FATH','Father'),
(3,1,'MOTH','Mother'),
(4,1,'HALF','Half Sibling'),
(5,1,'HUSB','Husband'),
(6,1,'WIFE','Wife'),
(7,1,'CHLD','Enfant'),
(8,1,'CUSN','Cousin'),
(9,1,'DIV','Ex Epoux'),
(10,1,'PART','Partner'),
(11,1,'FIAN','Fiance'),
(12,1,'ADOP','Adopted'),
(13,1,'ILLE','Illegitimate'),
(14,1,'1ST','1st Cousin'),
(15,1,'2ND','2nd Cousin'),
(16,1,'3RD','Third Cousin'),
(17,1,'GONE','Gone Cousin'),
(18,1,'ONCE','Once Removed Cousin'),
(19,1,'STEP','Step Sibling'),
(20,1,'UNKN','Unknown');

-- Table structure for table `synonyms`

DROP TABLE IF EXISTS `synonyms`;
CREATE TABLE `synonyms` (
  `syn_id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY (`syn_id`)
);

-- Table structure for table `taxonomy_terms`

DROP TABLE IF EXISTS `taxonomy_terms`;
CREATE TABLE `taxonomy_terms` (
  `term_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category_id` int DEFAULT NULL,
  PRIMARY KEY (`term_id`)
);

CREATE TABLE users_permissions (
    permission_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    tree_id INT NOT NULL,
    can_view BOOLEAN NOT NULL DEFAULT 0,
    can_edit BOOLEAN NOT NULL DEFAULT 0,
    is_admin BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
);

-- Table structure for table `users`

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);
