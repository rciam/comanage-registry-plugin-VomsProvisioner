# comanage-registry-plugin-VomsProvisioner
This is [COmanage Provisioner plugin](https://spaces.at.internet2.edu/display/COmanage/Provisioning+From+Registry) that will push CO Person changes into VOMS. The plugin is compatible with both VOMS SOAP and REST API. Also it premises that the handled VOs are mapped/modeled as COU entities in COmanage.

## Installation
1. Run `git clone https://github.com/rciam/comanage-registry-plugin-VomsProvisioner.git /path/to/comanage/local/Plugin/VomsProvisioner`
2. Run `cd /path/to/comanage/app`
3. Run `Console/clearcache`
4. Run `Console/cake schema create --file schema.php --path /path/to/comanage/local/Plugin/VomsProvisioner/Config/Schema`

In case step `4` fails deploy the tables using schema.sql
```sql
psql -h 127.0.0.1 -U registry_admin_db_user -d db_registry -f -a schema.sql
```

## Schema update
Not yet implemented

## Configuration
1. [Add a Provisioning Target](https://spaces.at.internet2.edu/display/COmanage/Provisioning+From+Registry#ProvisioningFromRegistry-AddingaProvisioningTarget) of type VomsProvisioner
2. Configure the provisioner
   * Add VOMS server
   * Load the Certificate Registered in VOMS
   * Load the Private Key paired with the Certificate loaded above
     * Key and Ceritificate must be associated with an Administrator user in VOMS
   * Enable/Disable OpenSSL syntax. Default to RFC2253 syntax (**experimental**)
   * Choose minimum level of Assurance requirement
   * Provide the Subject DN of the Actor(Robot User), in case User's Certificate has no DN for the Certificate Authority
   * Provide the Certificate Model, Subject Column and Issuer Column Name
![VOMS Provisioner Configuration](Documentation/images/voms_provisioner_configuration_v3.png)
## Compatibility matrix

This table matches the Plugin version with the supported COmanage version.

| Plugin |  COmanage |    PHP    |  VOMS  |
|:------:|:---------:|:---------:|:------:|
| v0.1.x | v3.1.x    | &gt;=v5.6 |  3.7.0 |
| v0.2.x | v3.1.x    | &gt;=v5.6 |  3.7.0 |
| v0.3.x | v3.1.x    | &gt;=v5.6 |  3.8.0 |


## Limitations
* Suspend User is not working through the API. As a workaround the plugin removes the user in case of a request for suspend.
* Remove Certificate is not working through the API (No workaround)
* Remove Attribute Class is not working through the API (No workaround)

## License

Licensed under the Apache 2.0 license, for details see [LICENSE](https://github.com/rciam/comanage-registry-plugin-VomsProvisioner/blob/master/LICENSE).