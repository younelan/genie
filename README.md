# Genie - Genealogy php web app

Genie is a rudimentary php web app to track ancestors, genealogy tree and relationships with **GEDCOM** Import/Export.

You can have multiple trees and in theory once auth is enabled, you can use it collaboratively. for now meant to be local. Not recommend putting this on the internet as is

**NOTE**: This app was rewritten to be more compliant with GEDCOM format. 

As is, no warranties. This is being rewritten to be more gedcom compliant and as a result

Until I have vetted the app more carefully, I have removed auth.

checkout [The history file](history.md) for changes

## Latest stable version
- the latest stable version: v0.4 (git tag)
- current version is in doc/version

## Version History:
     - Version 0.4.1
         - Tags For other relationships
         - Tags For families
         - Tags for individuals
     - Version 0.4
         - Gedcom Read
         - Gedcom Write
         - Fixed Adding new other relationships
         - use relcode instead of relationship_type_id
         - local react, tailwind
         - Footer everywhere/component
         - Empty Tree before delete
         - Synonym Editor
     - Bump to Version 0.3
         - 0.3 ui rewrite + api fetch
         - 0.2 families/gedcom compliant
         - 0.1 initial version

## Features:
- multiple trees
- Multiple people can have as many relationships as possible
- React Frontend
- Headless/API
- optionally display the tree using D3
- translation in french or english
- export to gedcom

## Todo:
- Add auth
- permissions to edit tree per user
- check for input, this was meant to be run localhost
- add custom fields to both the relationship and the people
- refactor into classes, more mvc, better folder structure

## Setup
- checkout pre-template (latest stable version)
- enable php, pdo on your web folder
- put in a web accessible folder
- create a configuration file
  - copy data/default.json to data/config.json
  - modify config.json with correct db credentials
  - choose a language (fr or en)

- ```mysql -u youruser <schema.sql```
- lock down your server before user, there is no auth
- on the tree
    - edit - add members
        - add people to the tree
        - on the person page, add relationships betwen people
        - note: do not use edit relationship, it is buggy right now. Instead, just delete the relationship and create another one
    - view - view a family graphically using D3
Happy tracking

## Screenshots

## Family Tree Screenshots

Here are some screenshots related to the Family Tree application:

*   ![Add Parents Dialog](screenshots/AddParentsDialog.jpg)
*   ![Family Tree Member List](screenshots/FamilyTeeMemberList.jpg)
*   ![Add Relationship](screenshots/AddRelationship.jpg)
*   ![Family Tree List](screenshots/FamilyTreeList.jpg)
*   ![Edit Individual Details](screenshots/EditIndividualDetails.jpg)
*   ![Individual Families and Parents](screenshots/IndividualFamiliesAndParents.jpg)


