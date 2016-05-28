/* Homecare schema and entities */
/* Version 1.0 */

/* This script depends upon the Core DB Release 1.0 Tables and Procs */
/* execute Core DB Release 1.0.sql before executing this script */

CREATE TABLE IF NOT EXISTS `homecare`.`patient` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstName` VARCHAR(300) NULL,
  `middleName` VARCHAR(300) NULL,
  `lastName` VARCHAR(300) NOT NULL,
  `status` INT NULL,
  `tenantid` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_patient_tenant_idx` (`tenantid` ASC),
  CONSTRAINT `fk_patient_tenant`
    FOREIGN KEY (`tenantid`)
    REFERENCES `homecare`.`tenant` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

USE `homecare`;
DROP procedure IF EXISTS `addPatient`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE addPatient (firstName varchar(300), middleName varchar(300), lastName varchar(300), tenantid int)
BEGIN

	insert into patient(firstName,middleName,lastName,tenantid)
    values (firstName,middleName,lastName,tenantid);
    
	SELECT Last_Insert_ID() as newID; 
        

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getPatientById`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE getPatientById(id int, tenantid int, userid int)
BEGIN

	select id,firstName,lastName,middleName
    from
		patient
	where id=id
			and tenantid=tenantid;

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `updatePatient`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE updatePatient(id int, firstName varchar(100), middleName varchar(100), lastName varchar(100), tenantid int)
BEGIN

     UPDATE patient SET
          firstName = firstName,
          middleName = middleName,
          lastName = lastName
     WHERE
          id=id
          AND tenantid=tenantid;
END$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `homecare`.`addressType` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(100) NOT NULL,
  `tenantid` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_AddressType_Tenant_idx` (`tenantid` ASC),
  CONSTRAINT `fk_AddressType_Tenant`
    FOREIGN KEY (`tenantid`)
    REFERENCES `homecare`.`tenant` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);
    
insert into addressType(type,tenantid) values ('home',1);



