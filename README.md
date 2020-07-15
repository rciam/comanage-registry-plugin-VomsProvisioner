# comanage-registry-plugin-VomsProvisioner
VOMS admin Client


## Known limitations
- If you Add a User in VOMS and immediately try to remove, VOMS will crash
- If you Add a User in VOMS only with cn, ca, dn, email then the CN parameter will never be added to the user
- Create Group with description invokes Rest API which will fail
  - Error Message:
- If you Delete a User via Soap VOMSadmin deletes the User's Certificate but leaves the user behind.