# History for Opensitez Genie
Check out [The README file](README.md) for more info

## Backlog
	* Edit relationship types
	* Edit Synonyms
	* Simplicity
		* templates
		* Auth
		* Config
	* Translation
	* Use Simplicity for templates
	* tags for relationships
	* User registration 
	* Add permission for users
	* gedcom write/read
	* path between 2 people
	* need better visualisation, works ok until 100 people then looks things really get hard to read

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