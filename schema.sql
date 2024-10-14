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

-- Table structure for table `person_relationship`

DROP TABLE IF EXISTS `person_relationship`;
CREATE TABLE `person_relationship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `person_id1` int NOT NULL,
  `person_id2` int NOT NULL,
  `relationship_type_id` int NOT NULL,
  `relation_start` date DEFAULT NULL,
  `relation_end` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `optional_fields` json DEFAULT NULL,
  PRIMARY KEY (`id`)
);

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
CREATE TABLE FamilyChildren (
    family_id INT,
    child_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table structure for table `relationship_type`

DROP TABLE IF EXISTS `relationship_type`;
CREATE TABLE `relationship_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
);
INSERT INTO `relationship_type` (`id`, `family_tree_id`, `description`) VALUES
(1, 1, 'Fraternal'),
(2, 1, 'Parent'),
(3, 1, 'Friend'),
(4, 1, 'Half Sibling'),
(5, 1, 'Mariage'),
(6, 1, 'Fiancailles'),
(7, 1, 'Child'),
(8, 1, 'Cousin'),
(9, 1, 'Step-Parent');

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
