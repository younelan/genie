#Genie migration v0.1 -> v0.2

To be more gedcom compliant, migration is required

```
select concat(p1.first_name," ",p1.last_name),concat(p2.first_name," ",p2.last_name),t.code,r.* from person_relationship r
left join person p1 on r.person_id1 =p1.id
left join person p2 on r.person_id2=p2.id
left join relationship_type t on r.relationship_type_id = t.id
where t.code in ("HUSB","WIFE");
```

## Make sure relationships are compatible

because of the way gedcom format works, relationships are tracked by families and as a result relationships need no be prepared for migration

### No siblings

GEDCOM siblings are determined by families. As a result, all sibling links will be ignored. Because there is no way to guess parents of the child with multiple parents, As a result, make sure you put parents first and children are all children of the parent instead

### Put children left

For the migration to work, the script needs to find who is the parent and who is the childre. Make sure that in all children relationships, the child is on the left side of the relationship

one way to find children who were put on the right is finding people who have no children. You can do that with the following query:

```
SELECT p1.id, p1.first_name, p1.last_name
FROM person p1
WHERE NOT EXISTS (
  SELECT r.id
  FROM person_relationship r
  JOIN relationship_type t ON r.relationship_type_id = t.id
  WHERE r.person_id1 = p1.id AND t.code = 'CHLD'
);
```

### Child relations are CHLD

Previously, there was a way to add FATHER/MOTHER link. To be gedcom compliant, make sure the link between parent and children is CHLD

## Parents to Families
create families

```
INSERT INTO families f (`husband_id`,`wife_id`,r.`created_at`,r.`updated_at`)
SELECT r.relationship_id1,r.relationship_id2,r.created_at,r.updated_ad 
FROM person_relationship r
LEFT JOIN person p1 ON r.person_id1 =p1.id
LEFT JOIN person p2 ON r.person_id2=p2.id
LEFT JOIN relationship_type t ON r.relationship_type_id = t.id
WHERE t.code IN ("HUSB","WIFE");
```

## Delete husband/wife in old relationship table
```DELETE r 
FROM person_relationship r
LEFT JOIN relationship_type t ON r.relationship_type_id = t.id
WHERE t.code IN ("HUSB","WIFE");
```

## restore deleted husband wife from family in case you still need it
```
INSERT INTO person_relationship (person_id1,person_id2,created_at,updated_at,RELCODE,relationship_type_id,tree_id)
SELECT  `husband_id`,`wife_id`,f.`created_at`,f.`updated_at`, 'HUSB',5,1
FROM families f
```

## Add relationship code to avoid join
ALTER TABLE person_relationship
ADD COLUMN RELCODE VARCHAR(255);
UPDATE person_relationship r
LEFT JOIN relationship_type t ON r.relationship_type_id = t.id
SET
  r.RELCODE = t.code;
