CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optional: Roles can be stored in a separate table and linked via foreign key
CREATE TABLE family_tree (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);

CREATE TABLE gender (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(50) NOT NULL
);

CREATE TABLE person (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_tree_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(255) NOT NULL,
    date_of_death DATE,
    place_of_death VARCHAR(255),
    gender_id INT NOT NULL,
    spouse_id INT,
    optional_fields JSON,
);

CREATE TABLE relationship_type (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_tree_id INT NOT NULL,
    description VARCHAR(100) NOT NULL,
);

CREATE TABLE person_relationship (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_tree_id INT NOT NULL,
    person_id1 INT NOT NULL,
    person_id2 INT NOT NULL,
    relationship_type_id INT NOT NULL
/*    FOREIGN KEY (family_tree_id) REFERENCES family_tree(id),
    FOREIGN KEY (person_id1) REFERENCES person(id),
    FOREIGN KEY (person_id2) REFERENCES person(id),
    FOREIGN KEY (relationship_type_id) REFERENCES relationship_type(id),
    CONSTRAINT unique_relationship UNIQUE (person_id1, person_id2, relationship_type_id)*/
);

