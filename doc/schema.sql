DROP TABLE IF EXISTS `citations`;
CREATE TABLE `citations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `source_id` int NOT NULL,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `page` varchar(255) DEFAULT NULL,
  `text` longtext ,
  `tree_id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `event_id` int NOT NULL AUTO_INCREMENT,
  `tree_id` int NOT NULL,
  `gedcom_id` varchar(255) NOT NULL,
  `event_type` varchar(50)  DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_place_id` int DEFAULT NULL,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `description` longtext ,
  `data` json DEFAULT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`event_id`)
);

DROP TABLE IF EXISTS `families`;
CREATE TABLE `families` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tree_id` int NOT NULL,
  `gedcom_id` varchar(255)  DEFAULT NULL,
  `husband_id` int DEFAULT NULL,
  `wife_id` int DEFAULT NULL,
  `marriage_date` date DEFAULT NULL,
  `divorce_date` date DEFAULT NULL,
  `marriage_place_id` int DEFAULT NULL,
  `divorce_place_id` int DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gedcom_id` (`gedcom_id`)
);

DROP TABLE IF EXISTS `family_children`;
CREATE TABLE `family_children` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gedcom_id` varchar(255)  DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `child_id` int DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `gedcom_file`;
CREATE TABLE `gedcom_file` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `tree_id` int DEFAULT NULL,
  `original_filename` varchar(255) NOT NULL,
  `storage_filename` varchar(255) NOT NULL,
  `file_size` int NOT NULL,
  `imported` tinyint(1) NOT NULL,
  `uploaded_at` datetime NOT NULL,
  `imported_at` datetime DEFAULT NULL ,
  `preview` json DEFAULT NULL,
  `validation_errors` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A4411B0D7E3C61F9` (`owner_id`),
  KEY `IDX_A4411B0D78B64A2` (`tree_id`)
);

DROP TABLE IF EXISTS `individuals`;
CREATE TABLE `individuals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gedcom_id` varchar(255)  DEFAULT NULL,
  `tree_id` int NOT NULL,
  `first_name` varchar(255)  NOT NULL,
  `last_name` varchar(255)  NOT NULL,
  `source` varchar(255)  DEFAULT NULL,
  `alive` tinyint(1) NOT NULL DEFAULT '1',
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255)  DEFAULT NULL,
  `death_date` date DEFAULT NULL,
  `death_place` varchar(255)  DEFAULT NULL,
  `gender` varchar(1)  DEFAULT NULL,
  `data` json DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `media`;
CREATE TABLE `media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` longtext ,
  `media_type` varchar(50) DEFAULT NULL,
  `tree_id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `notes`;
CREATE TABLE `notes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `note_text` longtext ,
  `data` json DEFAULT NULL,
  `tree_id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `other_relationships`;
CREATE TABLE `other_relationships` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gedcom_id` varchar(100) DEFAULT NULL,
  `tree_id` int NOT NULL,
  `person_id1` int NOT NULL,
  `person_id2` int NOT NULL,
  `relation_start` date DEFAULT NULL,
  `relation_end` date DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` json DEFAULT NULL,
  `relcode` varchar(255)  DEFAULT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `pending_changes`;
CREATE TABLE `pending_changes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `old_value` longtext ,
  `new_value` longtext ,
  `status` varchar(20) NOT NULL,
  `reviewed_by_id` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` longtext ,
  `tree_id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `pending_deletions`;
CREATE TABLE `pending_deletions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int NOT NULL,
  `reason` longtext ,
  `status` varchar(20) NOT NULL,
  `reviewed_by_id` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` longtext ,
  `tree_id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `pending_relations`;
CREATE TABLE `pending_relations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `relation_type` varchar(50) NOT NULL,
  `from_entity_id` int NOT NULL,
  `to_entity_id` int NOT NULL,
  `action` varchar(10) NOT NULL,
  `status` varchar(20) NOT NULL,
  `reviewed_by_id` int DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `review_notes` longtext ,
  `tree_id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `places`;
CREATE TABLE `places` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tree_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `county` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `gedcom_id` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FEAF6C5578B64A2` (`tree_id`)
);

DROP TABLE IF EXISTS `source_links`;
CREATE TABLE `source_links` (
  `source_link_id` int NOT NULL AUTO_INCREMENT,
  `individual_id` int DEFAULT NULL,
  `family_id` int DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `source_id` int DEFAULT NULL,
  `data` json NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`source_link_id`)
);

DROP TABLE IF EXISTS `sources`;
CREATE TABLE `sources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `publication_info` longtext ,
  `tree_id` int NOT NULL,
  `created_by_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `synonyms`;
CREATE TABLE `synonyms` (
  `syn_id` int NOT NULL AUTO_INCREMENT,
  `tree_id` int NOT NULL,
  `key` varchar(100)  NOT NULL,
  `value` varchar(100)  NOT NULL,
  PRIMARY KEY (`syn_id`)
);

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `tag` varchar(100)  NOT NULL,
  `person_id` int DEFAULT NULL,
  `tree_id` int NOT NULL,
  PRIMARY KEY (`tag_id`)
);

DROP TABLE IF EXISTS `tree_access`;
CREATE TABLE `tree_access` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tree_id` int NOT NULL,
  `user_id` int NOT NULL,
  `permission` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3B09BCA578B64A2A76ED395E04992AA` (`tree_id`,`user_id`,`permission`)
);

DROP TABLE IF EXISTS `trees`;
CREATE TABLE `trees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int NOT NULL,
  `name` varchar(255)  NOT NULL,
  `description` longtext ,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data` json DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180)  NOT NULL,
  `password` varchar(255)  NOT NULL,
  `roles` json NOT NULL,
  `first` varchar(255) NOT NULL,
  `last` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `data` json NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
);
