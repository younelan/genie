# Genie - Genealogy php web app

Genie is a rudimentary php web app to track ancestors, genealogy tree and relationships.

**NOTE**: This app was rewritten to be more compliant with GEDCOM format. The migrate functionality is being deprecated and all one to one structure deprecated

As is, no warranties. This is being rewritten to be more gedcom compliant and as a result

You can have multiple trees and in theory once auth is enabled, you can use it collaboratively. for now meant to be local. Not recommend putting this on the internet as is

Until I have vetted the app more carefully, I have removed auth.

checkout [The history file](history.md) for changes

## Latest stable version
- the latest stable version: v0.1.1 (git tag)
- current version is in doc/version

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

- ![Family List](screenshot/family_list.jpg)
- ![Relationships](screenshot/relationships.jpg)
- ![List Trees](screenshot/list_trees.jpg)

