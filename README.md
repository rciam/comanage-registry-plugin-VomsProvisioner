# comanage-registry-plugin-VomsProvisioner
VOMS admin Client


## Known limitations
- If you Add a User in VOMS and immediately try to remove, VOMS will crash
- If you Add a User in VOMS only with cn, ca, dn, email then the CN parameter will never be added to the user
- Create Group with description invokes Rest API which will fail
  - Error Message:
- If you Delete a User via Soap VOMSadmin deletes the User's Certificate but leaves the user behind.

## Installation Instructions
- Delete the foreign Key in the cm_vos table
- Remove all references of the Vo Model in around the project
- Remove all configured instances of the plugin
- Alter the table in the database
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

Phase two
```sql
alter table cm_co_voms_provisioner_targets drop column port;
alter table cm_co_voms_provisioner_targets drop column host;

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