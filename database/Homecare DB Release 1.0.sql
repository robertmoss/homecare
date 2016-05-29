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
    
insert into addressType(type,tenantid) values ('Home',1);


CREATE TABLE IF NOT EXISTS `homecare`.`address` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `addressTypeId` INT NOT NULL,
  `addressLine1` VARCHAR(200) NULL,
  `addressLine2` VARCHAR(200) NULL,
  `city` VARCHAR(200) NULL,
  `stateOrProvince` VARCHAR(20) NULL,
  `postalCode` VARCHAR(20) NULL,
  `tenantid` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_address_tenantid_idx` (`tenantid` ASC),
  CONSTRAINT `fk_address_tenantid`
    FOREIGN KEY (`tenantid`)
    REFERENCES `homecare`.`tenant` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


USE `homecare`;
DROP procedure IF EXISTS `addAddress`;

DELIMITER $$
USE `homecare`$$

CREATE PROCEDURE addAddress(addressTypeId int, addressLine1 varchar(200), addressLine2 varchar(200), city varchar(50), stateOrProvince varchar(2), postalCode varchar(20), tenantid int)
BEGIN

     INSERT INTO address (
          addressTypeId,
          addressLine1,
          addressLine2,
          city,
          stateOrProvince,
          postalCode, tenantid)
     VALUES (addressTypeId,
          addressLine1,
          addressLine2,
          city,
          stateOrProvince,
          postalCode, tenantid);

     SELECT Last_Insert_ID() as newID;

END$$
DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getAddressById`;

DELIMITER $$
USE `homecare`$$

CREATE PROCEDURE getAddressById(id int, tenant int, userid int)
BEGIN

     SELECT id,
          addressTypeId,
          addressLine1,
          addressLine2,
          city,
          stateOrProvince,
          postalCode
     FROM
          Address
      WHERE
          id=id AND tenantid=tenantid;

END$$
DELIMITER ;

 

USE `homecare`;
DROP procedure IF EXISTS `updateAddress`;

DELIMITER $$
USE `homecare`$$

CREATE PROCEDURE updateAddress(id int, addressTypeId int, addressLine1 varchar(200), addressLine2 varchar(200), city varchar(50), stateOrProvince varchar(2), postalCode varchar(20), tenantid int)
BEGIN

     UPDATE address SET
          addressTypeId = addressTypeId,
          addressLine1 = addressLine1,
          addressLine2 = addressLine2,
          city = city,
          stateOrProvince = stateOrProvince,
          postalCode = postalCode
     WHERE
          id=id
          AND tenantid=tenantid;
END$$
DELIMITER ;

/* Stored Procedures for Patient*/

USE `homecare`;
DROP procedure IF EXISTS `getPatientById`;

DELIMITER $$
USE `homecare`$$

CREATE PROCEDURE getPatientById(id int, tenant int, userid int)
BEGIN

     SELECT id,
          firstName,
          middleName,
          lastName
     FROM
          Patient
      WHERE
          id=id AND tenantid=tenantid;

END$$
DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getAddressesByPatientId`;

DELIMITER $$
USE `homecare`$$

CREATE PROCEDURE getAddressesByPatientId(id int, tenant int, userid int)
BEGIN

END$$
DELIMITER ;

CREATE TABLE IF NOT EXISTS `homecare`.`patientAddress` (
     id INT NOT NULL AUTO_INCREMENT,
     patientId INT NOT NULL, 
     addressId INT NOT NULL, 
     PRIMARY KEY (`id`),
     INDEX `fk_patientAddress_patient_idx` (`patientId` ASC),
     INDEX `fk_patientAddress_address_idx` (`addressId` ASC),
     CONSTRAINT `fk_patientAddress_patient` FOREIGN KEY (`patientId`)
          REFERENCES `homecare`.`patient` (`id`)
          ON DELETE CASCADE
          ON UPDATE NO ACTION,
     CONSTRAINT `fk_patientAddress_address` FOREIGN KEY (`addressId`)
          REFERENCES `homecare`.`address` (`id`)
          ON DELETE CASCADE
          ON UPDATE NO ACTION);
     
USE `homecare`;
DROP procedure IF EXISTS `addPatient`;

DELIMITER $$
USE `homecare`$$

CREATE PROCEDURE addPatient(firstName varchar(100), middleName varchar(100), lastName varchar(100), tenantid int)
BEGIN

     INSERT INTO patient (
          firstName,
          middleName,
          lastName, tenantid)
     VALUES (firstName,
          middleName,
          lastName, tenantid);

     SELECT Last_Insert_ID() as newID;

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


USE `homecare`;
DROP procedure IF EXISTS `getAddressesByPatientId`;

DELIMITER $$
USE `homecare`$$

CREATE PROCEDURE getAddressesByPatientId(_id int, _tenantid int, userid int)
BEGIN

     SELECT
          T1.id,
          T1.addressTypeId,
          T1.addressLine1,
          T1.addressLine2,
          T1.city,
          T1.stateOrProvince,
          T1.postalCode     
FROM
          address T1
          INNER JOIN patientAddress T2 ON T1.id=T2.addressId
     WHERE
          T2.patientId=_id
          and T1.tenantid=_tenantid;

END$$
DELIMITER ;

/* End Patient stored procs */