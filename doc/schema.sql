DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_place_id` int DEFAULT NULL,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `description` text,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`)
)

--
-- Table structure for table `families`
--

DROP TABLE IF EXISTS `families`;
CREATE TABLE `families` (
  `family_id` int NOT NULL AUTO_INCREMENT,
  `tree_id` int NOT NULL,
  `gedcom_id` varchar(20) DEFAULT NULL,
  `husband_id` int DEFAULT NULL,
  `wife_id` int DEFAULT NULL,
  `marriage_date` date DEFAULT NULL,
  `divorce_date` date DEFAULT NULL,
  `marriage_place_id` int DEFAULT NULL,
  `divorce_place_id` int DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`family_id`),
  UNIQUE KEY `gedcom_id` (`gedcom_id`)
)

--
-- Table structure for table `family_children`
--

DROP TABLE IF EXISTS `family_children`;
CREATE TABLE `family_children` (
  `family_relation_id` int NOT NULL AUTO_INCREMENT,
  `tree_id` int NOT NULL,
  `gedcom_id` varchar(100) DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `child_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `RELCODE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`family_relation_id`)
)

--
-- Table structure for table `family_tree`
--

DROP TABLE IF EXISTS `family_tree`;
CREATE TABLE `family_tree` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)

--
-- Table structure for table `gender`
--

DROP TABLE IF EXISTS `gender`;
CREATE TABLE `gender` (
  `id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
)

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `note_id` int NOT NULL AUTO_INCREMENT,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `note_text` text,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`note_id`)
)

--
-- Table structure for table `people_tags`
--

DROP TABLE IF EXISTS `people_tags`;
CREATE TABLE `people_tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(100) NOT NULL,
  `person_id` int DEFAULT NULL,
  `family_tree_id` int NOT NULL,
  PRIMARY KEY (`tag_id`)
)

--
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gedcom_id` varchar(100) DEFAULT NULL,
  `family_tree_id` int NOT NULL,
  `title` varchar(20) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(150) CHARACTER DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `alias1` varchar(150) DEFAULT NULL,
  `alias2` varchar(150) DEFAULT NULL,
  `alias3` varchar(150) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `alive` tinyint(1) NOT NULL DEFAULT '1',
  `preferred_name` varchar(100) DEFAULT NULL,
  `native_name` varchar(100) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `date_of_death` date DEFAULT NULL,
  `place_of_death` varchar(255) DEFAULT NULL,
  `gender_id` int DEFAULT NULL,
  `optional_fields` json DEFAULT NULL,
  `body` text ,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `passed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
)

--
-- Table structure for table `person_relationship`
--

DROP TABLE IF EXISTS `person_relationship`;
CREATE TABLE `person_relationship` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gedcom_id` varchar(100) DEFAULT NULL,
  `family_tree_id` int NOT NULL,
  `person_id1` int NOT NULL,
  `person_id2` int NOT NULL,
  `relationship_type_id` int NOT NULL,
  `relation_start` date DEFAULT NULL,
  `relation_end` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `optional_fields` json DEFAULT NULL,
  `RELCODE` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
)

--
-- Table structure for table `relationship_type`
--

DROP TABLE IF EXISTS `relationship_type`;
CREATE TABLE `relationship_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `code` varchar(32) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
)

--
-- Table structure for table `source_links`
--

DROP TABLE IF EXISTS `source_links`;
CREATE TABLE `source_links` (
  `source_link_id` int NOT NULL AUTO_INCREMENT,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`source_link_id`)
)

--
-- Table structure for table `sources`
--

DROP TABLE IF EXISTS `sources`;
CREATE TABLE `sources` (
  `source_id` int NOT NULL AUTO_INCREMENT,
  `source_title` varchar(255) DEFAULT NULL,
  `source_text` text,
  `data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`source_id`)
)

--
-- Table structure for table `synonyms`
--

DROP TABLE IF EXISTS `synonyms`;
CREATE TABLE `synonyms` (
  `syn_id` int NOT NULL AUTO_INCREMENT,
  `family_tree_id` int NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(100) NOT NULL,
  PRIMARY KEY (`syn_id`)
)

--
-- Table structure for table `taxonomy_terms`
--

DROP TABLE IF EXISTS `taxonomy_terms`;
CREATE TABLE `taxonomy_terms` (
  `term_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `category_id` int DEFAULT NULL,
  PRIMARY KEY (`term_id`)
)

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
)

