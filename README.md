# comanage-registry-plugin-VomsProvisioner
VOMS admin Client


## Known limitations
- If you Add a User in VOMS and immediately try to remove, VOMS will crash(you need to wait for until one minute to pass)
  - If you perform the same action through SOAP then it will not crash but will remove only the Certificate and leave the user behind
- If you Add a User in VOMS only with cn, ca, dn, email then the CN parameter will never be added to the user
- Create Group with description invokes Rest API which will fail
  - Error Message:

## Installation Instructions
- Delete the foreign Key in the cm_vos table
- Remove all references of the Vo Model in around the project
- Remove all configured instances of the plugin
- Alter the table in the database

Phase I
```sql
alter table cm_co_voms_provisioner_targets rename column server_url to host;
alter table cm_co_voms_provisioner_targets alter column host type varchar(256);
alter table cm_co_voms_provisioner_targets rename column vo_name to vo;
alter table cm_co_voms_provisioner_targets alter column vo type varchar(96);
alter table cm_co_voms_provisioner_targets rename column entity_type to robot_cert;
alter table cm_co_voms_provisioner_targets alter column robot_cert type text;
alter table cm_co_voms_provisioner_targets add column robot_key text;
alter table cm_co_voms_provisioner_targets add column port integer;
```

Phase II
```sql
alter table cm_co_voms_provisioner_targets drop column port;
alter table cm_co_voms_provisioner_targets drop column host;

-- COManage must be able to read and write on the cm_co_voms_provisioner_servers table 
CREATE TABLE cm_co_voms_provisioner_servers
(
    id                            serial PRIMARY KEY,
    co_voms_provisioner_target_id integer NOT NULL,
    host                          varchar(256),
    port                          integer,
    dn                            varchar(256),
    created                       timestamp without time zone,
    modified                      timestamp without time zone
);

-- Add Foreign Key constraints
ALTER TABLE ONLY public.cm_co_voms_provisioner_servers
    ADD CONSTRAINT cm_co_voms_provisioner_servers_co_voms_provisioner_target_id_fkey FOREIGN KEY (co_voms_provisioner_target_id) REFERENCES public.cm_co_voms_provisioner_targets (id);

```

Phase III
```sql
alter table cm_co_voms_provisioner_servers add column protocol varchar(5);
```