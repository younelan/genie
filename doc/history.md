# History for Opensitez Genie
Check out [The README file](README.md) for more info

## Backlog
	* User registration 
	* Add permission for users
	* path between 2 people
	* need better visualisation, works ok until 100 people then looks things really get hard to read


# current
    - tags
        - new tag endpoint
        - tags now have a type
        - individual tags are INDI
        - family tags are FAM
        - tags for other relationships are REL
    - Edit Synonyms
    - No simplicity for auth, now react
    - Gedcom Support
        - closed: gedcom import
        - closed: gedcom export
	- Simplicity
		- Auth
		- Config
    - Bump to 0.4 as Gedcom Support works
## 20250210
     - updated version to v0.3, ui rewrite
     - unified navigation component with menus
     - using relcode and api provided relationship types from api
     - Bug Fix: Creating a new other wasn't creating person
     - GedcomModel: successful exports to gedcom
     - Fixed autocomplete for parent
     - Fixed Adding of other relationship
     - remove child from family
     - remove parents from individual
     - remove spouse and delete family work
     - modal to edit other relationships
     - api
         - individuals
         - families
     - Reimplementation of old features as react
         - Visualization of descendants
         - Add Relationship modal
         - Visualization of family tree
         - Navigation on a tree
         - Add tree member
         - fix tags in edit_relationship
         - fix drop down for member details and spouse details
         - index.php now only serves the index page, everything is in the api folder
     - Retired edit relationship types: using gedcom types
     - Retired simplicity template: use react interacting with api
## 20250201
     - Began compatibility effort between Symfony version and This version
         - Database Structure is now compatible
         - Begin rewrite of the UI as React interacting with API
             - ported tree list
             - ported list of family members
             - partial port of member details, view only
     - Add Relationship modal is now a modal
     - Other Relationship view cleanup
     - Other Relationship add, Spouse Add, Children Add

## 20250128
     - Rewrote the add relationship as a modal so that it works better on mobile
         - Spouses to be added as Families, existing or new member
         - Children to be added to existing families
         - Possibility to add parents, existing/new
         - Cousins, Siblings can be added to old relationship table
     - Titles are now H5
     - Relationship.js is now broken down into multiple files for easier editing
     - New Family Controller/ Model... member controller getting to big
     - translate add relationship form
     - all translations are now passed to edit_member for better translation
     - Add Relationship is now at the beginning of the form for mobile support
     - autocompleteMember passes the tree so that people are part of current tree
     - Bootstrap is now version 5

## 20250101
    - Breaking change: switched to individuals table which is more gedcom compliant
        - no more middle_name, body, alias1, alias2, alias3 - not gedcom fields
        - date_of_birth, place_of_birth, date_of_death become birth_place,birth_date
        - place_of_death and deate_of_death become death_place,death_date
        - title is now stored in tags as notes, not a gedcom field
        - middle name, body are now notes to be more gedcom compliant
        - gender is now M/F vs 1/2 to be gedcom compliant. using gender field vs gender_id. 
           - create a varchar(1) gender field;
           - update individuals set gender="M" where gender_id=1;
           - update individuals set gender="F" where gender_id=1;
        - would have liked to keep old structure but I prefer doing it now when no one using it, data has been migrated using Migrate.php which allows to migrate from old to new structure
    - updated edit person form
        - form edited to add children, parents, spouses rather than individual relationships
        - legacy person_relationship hidden in the "more fields"
        - optimized person info to display better on mobile
        - updated form to remove removed fields (middle name, body, title,  
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
