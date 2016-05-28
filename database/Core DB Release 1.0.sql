CREATE SCHEMA IF NOT EXISTS `homecare` DEFAULT CHARACTER SET latin1 ;

use homecare;

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(300) DEFAULT NULL,
  `password` varchar(300) NOT NULL,
  `twitterHandle` varchar(100) DEFAULT NULL,
  `activationToken` varchar(225) DEFAULT NULL,
  `lastActivationRequest` int(11) DEFAULT NULL,
  `lostPasswordRequest` int(1) DEFAULT NULL,
  `active` int(1) DEFAULT NULL,
  `signUpDate` datetime DEFAULT NULL,
  `lastSignIn` datetime DEFAULT NULL,
  `passwordExpires` datetime DEFAULT NULL,
  `bio` text,
  PRIMARY KEY (`id`),
  KEY `ix_user_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=latin1;

insert into user(id,name,email,password) values (1,'admin','mossr19@gmail.com','b9fb299b05449a732b7be3f35f3179277b868c4bcd1f0c77be278c5564e11a788');

insert into tenant(id,name,title,welcome,allowAnonAccess)
values (1,"base","Homecare","Weclome to Homecare",0);

update user set active=1 where id=1;

CREATE TABLE IF NOT EXISTS `tenant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `welcome` text,
  `finditem` varchar(100) DEFAULT NULL,
  `css` varchar(1000) DEFAULT NULL,
  `allowAnonAccess` bit(1) DEFAULT b'0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

ALTER TABLE `homecare`.`tenant` 
DROP COLUMN `finditem`;


CREATE TABLE IF NOT EXISTS `tenantSetting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenantid` int(11) DEFAULT NULL,
  `setting` varchar(100) NOT NULL,
  `value` text,
  PRIMARY KEY (`id`),
  KEY `ix_tenantSetting_setting` (`setting`) USING BTREE,
  KEY `fk_tenantSetting_tenant_idx` (`tenantid`),
  CONSTRAINT `fk_tenantSetting_tenant` FOREIGN KEY (`tenantid`) REFERENCES `tenant` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

CREATE TABLE `tenantUser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `tenantid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tenantUser_user_idx` (`userid`),
  KEY `fk_tenantUser_tenant_idx` (`tenantid`),
  CONSTRAINT `fk_tenantUser_tenant` FOREIGN KEY (`tenantid`) REFERENCES `tenant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_tenantUser_user` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;

CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

insert into role(name) values ('standard');

insert into role(name) values ('admin');

CREATE TABLE `tenantUserRole` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenantuserid` int(11) NOT NULL,
  `roleid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_Role_idx` (`roleid`),
  KEY `fk_TenantUser` (`tenantuserid`),
  CONSTRAINT `fk_Role` FOREIGN KEY (`roleid`) REFERENCES `role` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TenantUser` FOREIGN KEY (`tenantuserid`) REFERENCES `tenantUser` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;

USE `homecare`;
DROP procedure IF EXISTS `validateUser`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE `validateUser`(email varchar(300), password varchar(300), tenantid int)
begin

	set @userid = 0;
    set @pwd = "";

	select id into @userid 
		from user as u
	where 
		u.email=email
		and (u.password=password or u.password='reset');
	
    if (@userid>0) then
		update user set lastSignIn=current_timestamp() where id=@userid;
	end if;

	select 	u.id as userid,
			u.name as name,
            IF(u.password="reset", 1, 0) as resetPassword,
			u.email as email,
			u.twitterHandle as twitterHandle,
			u.active as active,
			coalesce(u.lastSignIn,"never") as lastSignIn,
            u.passwordExpires as passwordExpires
	from user as u
		where u.email=email
			and (u.password=password or u.password='reset');


END$$

DELIMITER ;

CREATE TABLE IF NOT EXISTS `tenantProperty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenantid` int(11) NOT NULL,
  `entity` varchar(100) NOT NULL,
  `property` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_tenantProperty_tenant_idx` (`tenantid`),
  CONSTRAINT `fk_tenantProperty_tenant` FOREIGN KEY (`tenantid`) REFERENCES `tenant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
SELECT * FROM food.tenantProperty;

CREATE TABLE IF NOT EXISTS `categoryType` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `tenantid` int(11) NOT NULL,
  `categorytypeid` int(11) DEFAULT NULL,
  `seq` int(11) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_category_to_tenant_idx` (`tenantid`),
  KEY `FK_category_to_categorytype_idx` (`categorytypeid`),
  CONSTRAINT `FK_category_to_categorytype` FOREIGN KEY (`categorytypeid`) REFERENCES `categoryType` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `FK_category_to_tenant` FOREIGN KEY (`tenantid`) REFERENCES `tenant` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `menuItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `newWindow` bit(1) DEFAULT NULL,
  `tenantid` int(11) DEFAULT NULL,
  `seq` int(11) DEFAULT NULL,
  `roles` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_menuItem_tenant_idx` (`tenantid`),
  CONSTRAINT `fk_menuItem_tenant` FOREIGN KEY (`tenantid`) REFERENCES `tenant` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

USE `homecare`;
DROP procedure IF EXISTS `getMenuItemsByTenantID`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE `getMenuItemsByTenantID`(_tenantid int, callingtenantid int, callinguserid int)
BEGIN

	select id, name, link, newWindow, tenantid, seq, roles
    from 
		menuItem
	where
		tenantid=_tenantid
	order by seq;
        
END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getTenantRolesByUserId`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE `getTenantRolesByUserId`(_userid int, _tenantid int)
BEGIN

	select 
		R.id, R.name
	from 
		tenantUserRole TUR
		inner join tenantUser TU on TUR.tenantuserid=TU.id
		inner join role R on R.id=TUR.roleid
	where 
		TU.tenantid=_tenantid
		and TU.userid=_userid;

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getUsers`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE `getUsers`(userid int, numToReturn integer,startAt integer)
BEGIN

IF userid=1 THEN
	set @query = "select 
						id,name,email,active,coalesce(U.lastSignIn,""never"") as lastSignIn
 					from 
 						user U
					where 
						active=1
                        and U.id<>? and U.id<>?
					order by
						name
					limit ?,?;";
ELSE
	set @query = "select distinct
					U.id,U.name,U.email,U.active,coalesce(U.lastSignIn,""never"") as lastSignIn
				  from 
					user U
					inner join tenantUser TU on U.id=TU.userid
				where 
					active=1
					and U.id<>1
                    and U.id<>?
					and TU.tenantid in (select distinct tenantid from 
							tenantUser TU
								inner join tenantUserRole TUR on TU.id=TUR.tenantuserid
								inner join role R on R.id=TUR.roleid
							where
								R.name=""admin""
								and TU.userid=?)
				order by
					U.name
			    limit ?,?;";
END IF;


prepare stmt from @query;

set @start = startAt;
set @num = numToReturn;
set @userid = userid;

execute stmt using @userid, @userid,@start, @num;

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getUsersCount`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getUsersCount`(userid int)
BEGIN

IF userid=1 THEN
	set @query = "select 
						count(*)
 					from 
 						user U
					where 
						active=1
                        and U.id<>? and U.id<>?"; 
ELSE
	set @query = "select count(*)
				  from 
					user U
					inner join tenantUser TU on U.id=TU.userid
				where 
					active=1
					and U.id<>1
                    and U.id<>?
					and TU.tenantid in (select distinct tenantid from 
							tenantUser TU
								inner join tenantUserRole TUR on TU.id=TUR.tenantuserid
								inner join role R on R.id=TUR.roleid
							where
								R.name=""admin""
								and TU.userid=?)";
END IF;


prepare stmt from @query;

set @userid = userid;

execute stmt using @userid,@userid;

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `addUser`;

DELIMITER $$
USE `homecare`$$
CREATE  PROCEDURE `addUser`(_name varchar(100), email varchar(300), _password varchar(300), bio text, tenantid int)
BEGIN

	INSERT INTO user(name, email,password,active)
			VALUES (_name, email, _password,1);

	SELECT Last_Insert_ID() as newID; 


END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `addTenantUserRole`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE `addTenantUserRole`(_userid int, _tenantid int, _role varchar(200))
BEGIN

	-- resolve role id
    declare roleid int;
    declare tenantuserid int;
    
    select id into @roleid from role where name=_role;
    
    insert into tenantUser(userid,tenantid) values (_userid,_tenantid);
    
    SELECT Last_Insert_ID() into @tenantuserid;
    
    insert into tenantUserRole(tenantuserid,roleid) values (@tenantuserid,@roleid);

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getRolesByUserId`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getRolesByUserId`(_userid int)
BEGIN

	select 
		T.name as tenant, R.id, R.name as role
	from 
		tenantUserRole TUR
		inner join tenantUser TU on TUR.tenantuserid=TU.id
		inner join role R on R.id=TUR.roleid
        inner join tenant T on TU.tenantid=T.id
	where 
	TU.userid=_userid;

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `deleteUser`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteUser`(_targetid int, tenantid int, _byuserid int)
BEGIN
	
	
	update user set active=0 where id=_targetid;

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `removeTenantUsers`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `removeTenantUsers`(_userid int)
BEGIN

	delete from tenantUser where userid=_userid;

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getTenants`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE `getTenants`(userid int, numToReturn integer,startAt integer)
BEGIN

set @start = startAt;
set @num = numToReturn;
set @userid = userid;

if userid=1 then
	prepare stmt from "select 
						T.id,T.name
 					from 
 						tenant T
                    order by
						T.name
					limit ?,?";                
	execute stmt using @start, @num;
else
	prepare stmt from "select 
						T.id,T.name
 					from 
 						tenant T
                        inner join tenantUser TU on T.id=TU.tenantid
						inner join tenantUserRole TUR on TUR.tenantuserid = TU.id
                        inner join role R on TUR.roleid=R.id
                    where R.name=""admin""
						and userid=?
                    order by
						T.name
					limit ?,?";
	execute stmt using @userid, @start, @num;
end if;


END$$

DELIMITER ;


USE `homecare`;
DROP procedure IF EXISTS `getTenantById`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getTenantById`(id int, tenantid int, userid int)
BEGIN

prepare stmt from "select 
						T.id as id,
						T.name as name,
                        T.title as title,
                        T.welcome as welcome,
                        T.css as css,
                        T.allowAnonAccess as allowAnonAccess
 					from 
 						tenant T
					where T.id = ?";

set @tenantid = tenantid;
set @id = id;

execute stmt using @id;



END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getSettingsByTenantID`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getSettingsByTenantID`(_tenantid int, callingtenantid int, callinguserid int)
BEGIN

	select id, tenantid , setting as name, value 
    from 
		tenantSetting
	where
		tenantid=_tenantid;
        
END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getPropertiesByTenantID`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getPropertiesByTenantID`(_tenantid int, callingtenantid int, callinguserid int)
BEGIN

	select id, tenantid , entity, property as name
    from 
		tenantProperty
	where
		tenantid=_tenantid;
        
END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `getCategoriesByTenantID`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getCategoriesByTenantID`(_tenantid int, callingtenantid int, callinguserid int)
BEGIN

	select id, tenantid, name, categorytypeid, seq, icon
    from 
		category
	where
		tenantid=_tenantid;
        
END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `addTenant`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `addTenant`(_name varchar(100), _title varchar(100), _welcome text,  _css varchar(1000), _allowAnonAccess bit, tenantid int)
BEGIN

	insert into tenant(name,title,welcome,css,allowAnonAccess)
	values (_name,_title,_welcome,_css, _allowAnonAccess);

	SELECT Last_Insert_ID() as newID; 

END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `updateTenant`;

DELIMITER $$
USE `homecare`$$
CREATE PROCEDURE `updateTenant`(_id int, _name varchar(100), _title varchar(100), _welcome text,  _css varchar(1000), _allowAnonAccess bit,_tenantid int)
BEGIN

	update tenant set
		name = _name,
        title = _title,
        welcome = _welcome,
        css = _css,
        allowAnonAccess = _allowAnonAccess
	where
		id = _id;

END$$

DELIMITER ;


USE `homecare`;
DROP procedure IF EXISTS `addTenantProperty`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `addTenantProperty`(targettenantid int, _entity varchar(100), _property varchar(255), callingtenantid int)
BEGIN

	insert into tenantProperty(tenantid,entity,property)
    values (targettenantid, _entity, _property);
    
    SELECT Last_Insert_ID() as newID; 


END$$

DELIMITER ;

USE `homecare`;
DROP procedure IF EXISTS `addTenantSetting`;

DELIMITER $$
USE `homecare`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `addTenantSetting`(targettenantid int, setting varchar(100), _value text, callingtenantid int)
BEGIN

	insert into tenantSetting(tenantid,setting,value)
    values (targettenantid, setting, _value);
    
    SELECT Last_Insert_ID() as newID; 


END$$

DELIMITER ;























