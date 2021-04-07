# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.2] - 2021-04-07
### Fixed
- Buggy Regex check when choosing the appropriate Certificate for VOMs

## [0.3.1] - 2021-04-05
### Fixed
- Add the user multiple time in to the same VO(COU)

## [0.3.0] - 2021-04-02
### Added
- Made Certificate Model Name, Certificate Subject DN column name, Certificate Issuer DN column name configurable
- Added configuration for minimum level of Assurance

### Changed
- Provisioner will link the Provisioned Certificate to the corresponed CO Person Role(COU)
- Pick the Certificate linked under the delete CO Person Role to use for user (de)provisioning

### Fixed
- Extract CO Person Role ID during a a petition


## [0.2.0] - 2020-10-14
### Added
- Made default DN for the Certificate Authority configurable

## [0.1.4] - 2020-10-12
### Fixed
- Unhandled group membership with no COU id during provisioning
- Use Robot CA as the default CA if User's Personal Certificate has no CA

## [0.1.3] - 2020-10-02
### Fixed
- Flow broke if CO Person profile updated fields other than CO Person Role and Status

## [0.1.2] - 2020-09-30
### Fixed
- Allow VomsProvisioner's actions if a CO Person action has fired or if a COU action fired with the same name as in the configuration

## [0.1.1] - 2020-09-29
### Added
- CO Person global suspended status and COU/VO suspended status result to a delete from VO for the CO Person(Workaround due to VOMS v3.7.0 API limitations)

### Fixed
- PHP fatal error

## [v0.1.0] - 2020-09-29
### Added
- COmanage provisioner compatible with VOMS SOAP and REST API
