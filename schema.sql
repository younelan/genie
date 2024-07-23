
CREATE TABLE `family_tree` (
  `id` int NOT NULL,
  `owner_id` int NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `gender` (
  `id` int NOT NULL,
  `description` VARCHAR(50) NOT NULL
);

CREATE TABLE `synonyms` (
  `syn_id` int NOT NULL,
  `tree_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `synonym` varchar(100) NOT NULL
);

CREATE TABLE `person` (
  `id` int NOT NULL,
  `family_tree_id` int NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `alias1` VARCHAR(100) DEFAULT NULL,
  `alias2` VARCHAR(100) DEFAULT NULL,
  `alias3` VARCHAR(100) DEFAULT NULL,
  `preferred_name` varchar(100) DEFAULT NULL,
  `native_name` varchar(100) DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `body` VARCHAR(4000) DEFAULT NULL,
  `place_of_birth` VARCHAR(255) DEFAULT NULL,
  `date_of_death` DATE DEFAULT NULL,
  `place_of_death` VARCHAR(255) DEFAULT NULL,
  `gender_id` int DEFAULT NULL,
  `spouse_id` int DEFAULT NULL,
  `optional_fields` json DEFAULT NULL
  `body` text CHARACTER DEfAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `passed` tinyint(1) DEFAULT NULL
); 

CREATE TABLE `person_relationship` (
  `id` int NOT NULL,
  `family_tree_id` int NOT NULL,
  `person_id1` int NOT NULL,
  `person_id2` int NOT NULL,
  `relationship_start` DATE DEFAULT NULL,
  `relationship_end` DATE DEFAULT NULL,
  `optional_fields` json DEFAULT NULL,
  `relationship_type_id` int NOT NULL
); 

CREATE TABLE `relationship_type` (
  `id` int NOT NULL,
  `family_tree_id` int NOT NULL,
  `description` VARCHAR(100) NOT NULL,
  `optional_fields` json DEFAULT NULL
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

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE `family_tree`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `gender`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `person`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `person_relationship`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `relationship_type`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `family_tree`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `gender`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `person`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `person_relationship`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `relationship_type`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
