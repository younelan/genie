#Genie migration v0.1 -> v0.2

To be more gedcom compliant, migration is required

## Parents to Families


```
insert into families f (`husband_id`,`wife_id`,`created_at`,`updated_at`) VALUES (p1.id,p2.id,r.created_at,r.updated_at)
select concat(p1.first_name," ",p1.last_name),concat(p2.first_name," ",p2.last_name),t.code,r.* from person_relationship r
left join person p1 on r.person_id1 =p1.id
left join person p2 on r.person_id2=p2.id
left join relationship_type t on r.relationship_type_id = t.id
where t.code in ("HUSB","WIFE");
```
