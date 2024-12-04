# History for Opensitez Genie
Check out [The README file](README.md) for more info

## Backlog
	* Edit relationship types
	* Edit Synonyms
	* Simplicity
		* Auth
		* Config
	* Use Simplicity for templates
	* tags for relationships
	* User registration 
	* Add permission for users
	* gedcom write/read
	* path between 2 people
	* need better visualisation, works ok until 100 people then looks things really get hard to read

## 20241203
    - Gedcom Migration to the menu
    - Migrate Class that exports gedcom
    - Warnings when tree have issues
    - likely last version before rewrite

## 20241017
    - implemented twig template for all views
        - list_member
        - edit_member
        - list_trees
        - add_member
        - add_tree
        - view_tree
    - tree height/width from db
    - translated relationship types
    - fixed view_tree missing quote that was breaking visualization
    - fixed issue with missing menu tree in edit_member
    - schema change for easier GEDCOM integration
        - migration script to new schema (in progress)
            - create families/delete query
            - add relationship code to member (gedcom code)
        - updated schema
        - relationship code is now directly in relationship
## 20240908
    - Classes inherit base class
    - added translation function
    - add_tree, edit_member, list_member, edit_member now use translation
    - added translation for french, english
    - breaking change: configuration is moved from db.php to config.json so that additional params like lang can be added
    - to create a config file, copy data/default.json to data/config.json and adjust accordingly
    - merged defaults, db and includes into init.php
    - table names are now in default.json to make allow using different table names
    - add composer php-gedcom library to see if I can use it to import/export gedcom

## 20240730
	- Tag support for people
		- click on x deletes
		- pasting comma separated values works
		- commenting copy button because only works over ssl
		- type a tag, press comma moves to the next tag
	- Last Updated on right column
	- Updated schema for tags
	- updated schema automatically updates records on update
	- Synonyms for stats so that people with slightly mispelled names combine in stats

## 20240715
	- schema update : preferred name , alive, created_at, updated_at
	- Added Gender in list to see who needs an update
	- statistics by name
	- 3 column on desktop, more mobile friendly
	- fixed broken search
	- refactored:
		- lint for more psr compliant spacing
		- moving templates, models, controllers to subdirectories

## 20240630
	- Totals By Gender
	- Swap Relationships
	- More hidden fields
	- Using Bootstrap
	- Local jquery and Bootstrap
	- Including simplicity and composer to make things easier to update

## 20240614
	- Initial Release, works, many to many relationships
	- Add trees
	- Edit people
