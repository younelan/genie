# Genie - Genealogy php web app

Genie is a rudimentary php web app to track ancestors, genealogy tree and relationships.

Though you can have multiple trees and in theory once auth is in, you could use it collaboratively, for now it is meant to be run locally. I wouldn't recommend putting this on the internet as is

As is, no warranties.

Until I have vetted the app more carefully, I have removed auth.

## Features:
_ multiple trees
_ Multiple people can have as many relationships as possible
_ optionally display the tree using D3

## Todo:
_ Add auth
_ Buggy edit relationships
_ permissions
_ check for input

## Setup

- enable php, pdo on your web folder
- put in a web accessible folder
- ```mysql -u youruser <schema.sql```
- lock down your server before user, there is no auth

Happy tracking
