-- noinspection SqlNoDataSourceInspectionForFile

CREATE TABLE cm_co_voms_provisioner_targets
(
    id                            serial PRIMARY KEY,
    co_provisioning_target_id     integer NOT NULL,
    vo                            varchar(96),
    robot_cert                    text,
    robot_key                     text,
    openssl_syntax                boolean,
    ca_dn_default                 varchar(512),
    created                       timestamp without time zone,
    modified                      timestamp without time zone
);

CREATE UNIQUE INDEX cm_co_voms_provisioner_targets_i1 ON public.cm_co_voms_provisioner_targets USING btree (co_provisioning_target_id);
-- Add Foreign Key constraints
ALTER TABLE ONLY public.cm_co_voms_provisioner_targets
    ADD CONSTRAINT cm_co_voms_provisioner_targets_co_provisioning_target_id_fkey FOREIGN KEY (co_provisioning_target_id) REFERENCES public.cm_co_provisioning_targets (id);


CREATE TABLE cm_co_voms_provisioner_servers
(
    id                            serial PRIMARY KEY,
    co_voms_provisioner_target_id integer NOT NULL,
    host                          varchar(256),
    protocol                      varchar(5),
    port                          integer,
    dn                            varchar(256),
    created                       timestamp without time zone,
    modified                      timestamp without time zone
);

-- Add Foreign Key constraints
ALTER TABLE ONLY public.cm_co_voms_provisioner_servers
    ADD CONSTRAINT cm_co_voms_provisioner_servers_co_voms_provisioner_target_id_fkey FOREIGN KEY (co_voms_provisioner_target_id) REFERENCES public.cm_co_voms_provisioner_targets (id);