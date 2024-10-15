#Genie migration v0.1 -> v0.2

To be more gedcom compliant, migration is required

```
select concat(p1.first_name," ",p1.last_name),concat(p2.first_name," ",p2.last_name),t.code,r.* from person_relationship r
left join person p1 on r.person_id1 =p1.id
left join person p2 on r.person_id2=p2.id
left join relationship_type t on r.relationship_type_id = t.id
where t.code in ("HUSB","WIFE");
```

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

