SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `TICKET_SYSTEM` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `TICKET_SYSTEM` ;

-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`USER_ROLE`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`USER_ROLE` (
  `ROL_ID` INT NOT NULL AUTO_INCREMENT,
  `ROL_VALUE` VARCHAR(45) NULL,
  PRIMARY KEY (`ROL_ID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`USER`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`USER` (
  `USR_ID` INT NOT NULL AUTO_INCREMENT,
  `USR_LOGIN` VARCHAR(45) NOT NULL,
  `USR_NAME` VARCHAR(45) NULL,
  `USR_SURNAME` VARCHAR(45) NULL,
  `USR_MAIL` VARCHAR(45) NULL,
  `USR_PASSWORD` VARCHAR(45) NOT NULL,
  `ROL_USR_ROLE` INT NOT NULL,
  PRIMARY KEY (`USR_ID`),
  INDEX `fk_USER_USER_ROLE1_idx` (`ROL_USR_ROLE` ASC),
  CONSTRAINT `fk_USER_USER_ROLE1`
    FOREIGN KEY (`ROL_USR_ROLE`)
    REFERENCES `TICKET_SYSTEM`.`USER_ROLE` (`ROL_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`STATUS`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`STATUS` (
  `STS_ID` INT NOT NULL AUTO_INCREMENT,
  `STS_VALUE` VARCHAR(45) NULL,
  `STS_IS_CLOSED` TINYINT NULL DEFAULT 0,
  PRIMARY KEY (`STS_ID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`PRIORITY`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`PRIORITY` (
  `PRT_ID` INT NOT NULL AUTO_INCREMENT,
  `PRT_VALUE` VARCHAR(45) NULL,
  PRIMARY KEY (`PRT_ID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`QUEUE`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`QUEUE` (
  `QUE_ID` INT NOT NULL AUTO_INCREMENT,
  `QUE_NAME` VARCHAR(45) NULL,
  PRIMARY KEY (`QUE_ID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`TICKET`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`TICKET` (
  `TCK_ID` INT NOT NULL AUTO_INCREMENT,
  `TCK_CREATION_DATE` DATETIME NULL,
  `TCK_CLOSED_DATE` DATETIME NULL,
  `TCK_TITLE` VARCHAR(45) NULL,
  `TCK_DESC` TEXT NULL,
  `USR_TCK_OWNER` INT NULL,
  `USR_TCK_AUTHOR` INT NULL,
  `STS_TCK_STATUS` INT NOT NULL,
  `PRT_TCK_PRIORITY` INT NOT NULL,
  `QUE_QUEUE` INT NOT NULL,
  PRIMARY KEY (`TCK_ID`),
  INDEX `fk_TICKET_USER_idx` (`USR_TCK_OWNER` ASC),
  INDEX `fk_TICKET_USER1_idx` (`USR_TCK_AUTHOR` ASC),
  INDEX `fk_TICKET_STATUS1_idx` (`STS_TCK_STATUS` ASC),
  INDEX `fk_TICKET_PRIORITY1_idx` (`PRT_TCK_PRIORITY` ASC),
  INDEX `fk_TICKET_QUEUE1_idx` (`QUE_QUEUE` ASC),
  CONSTRAINT `fk_TICKET_USER`
    FOREIGN KEY (`USR_TCK_OWNER`)
    REFERENCES `TICKET_SYSTEM`.`USER` (`USR_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_TICKET_USER1`
    FOREIGN KEY (`USR_TCK_AUTHOR`)
    REFERENCES `TICKET_SYSTEM`.`USER` (`USR_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_TICKET_STATUS1`
    FOREIGN KEY (`STS_TCK_STATUS`)
    REFERENCES `TICKET_SYSTEM`.`STATUS` (`STS_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_TICKET_PRIORITY1`
    FOREIGN KEY (`PRT_TCK_PRIORITY`)
    REFERENCES `TICKET_SYSTEM`.`PRIORITY` (`PRT_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_TICKET_QUEUE1`
    FOREIGN KEY (`QUE_QUEUE`)
    REFERENCES `TICKET_SYSTEM`.`QUEUE` (`QUE_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`COMMENT`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`COMMENT` (
  `CMT_ID` INT NOT NULL AUTO_INCREMENT,
  `CMT_VALUE` TEXT NULL,
  `CMT_CREATION_DATE` DATETIME NULL,
  `TCK_CMT_TICKET` INT NOT NULL,
  `USR_CMT_AUTHOR` INT NOT NULL,
  PRIMARY KEY (`CMT_ID`),
  INDEX `fk_COMMENT_TICKET1_idx` (`TCK_CMT_TICKET` ASC),
  INDEX `fk_COMMENT_USER1_idx` (`USR_CMT_AUTHOR` ASC),
  CONSTRAINT `fk_COMMENT_TICKET1`
    FOREIGN KEY (`TCK_CMT_TICKET`)
    REFERENCES `TICKET_SYSTEM`.`TICKET` (`TCK_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_COMMENT_USER1`
    FOREIGN KEY (`USR_CMT_AUTHOR`)
    REFERENCES `TICKET_SYSTEM`.`USER` (`USR_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`ATTACHMENT`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`ATTACHMENT` (
  `ATT_ID` INT NOT NULL AUTO_INCREMENT,
  `ATT_NAME` VARCHAR(45) NULL,
  PRIMARY KEY (`ATT_ID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`TICKET_has_ATTACHMENT`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`TICKET_has_ATTACHMENT` (
  `TCK_TICKET` INT NOT NULL,
  `ATT_ATTACHMENT` INT NOT NULL,
  PRIMARY KEY (`TCK_TICKET`, `ATT_ATTACHMENT`),
  INDEX `fk_TICKET_has_ATTACHMENT_ATTACHMENT1_idx` (`ATT_ATTACHMENT` ASC),
  INDEX `fk_TICKET_has_ATTACHMENT_TICKET1_idx` (`TCK_TICKET` ASC),
  CONSTRAINT `fk_TICKET_has_ATTACHMENT_TICKET1`
    FOREIGN KEY (`TCK_TICKET`)
    REFERENCES `TICKET_SYSTEM`.`TICKET` (`TCK_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_TICKET_has_ATTACHMENT_ATTACHMENT1`
    FOREIGN KEY (`ATT_ATTACHMENT`)
    REFERENCES `TICKET_SYSTEM`.`ATTACHMENT` (`ATT_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`COMMENT_has_ATTACHMENT`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`COMMENT_has_ATTACHMENT` (
  `CMT_COMMENT` INT NOT NULL,
  `ATT_ATTACHMENT` INT NOT NULL,
  PRIMARY KEY (`CMT_COMMENT`, `ATT_ATTACHMENT`),
  INDEX `fk_COMMENT_has_ATTACHMENT_ATTACHMENT1_idx` (`ATT_ATTACHMENT` ASC),
  INDEX `fk_COMMENT_has_ATTACHMENT_COMMENT1_idx` (`CMT_COMMENT` ASC),
  CONSTRAINT `fk_COMMENT_has_ATTACHMENT_COMMENT1`
    FOREIGN KEY (`CMT_COMMENT`)
    REFERENCES `TICKET_SYSTEM`.`COMMENT` (`CMT_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_COMMENT_has_ATTACHMENT_ATTACHMENT1`
    FOREIGN KEY (`ATT_ATTACHMENT`)
    REFERENCES `TICKET_SYSTEM`.`ATTACHMENT` (`ATT_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`ACTION_TYPE`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`ACTION_TYPE` (
  `TYP_ID` INT NOT NULL AUTO_INCREMENT,
  `TYP_VALUE` VARCHAR(45) NULL,
  `TYP_COMUNICATE` VARCHAR(255) NULL,
  PRIMARY KEY (`TYP_ID`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TICKET_SYSTEM`.`ACTION_FLOW`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `TICKET_SYSTEM`.`ACTION_FLOW` (
  `ACT_ID` INT NOT NULL AUTO_INCREMENT,
  `ACT_DATE` DATETIME NULL,
  `ACT_PREVIOUS_VALUE` INT NULL,
  `ACT_ACTUAL_VALUE` INT NULL,
  `TCK_TICKET` INT NOT NULL,
  `USR_CHANGE_AUTHOR` INT NOT NULL,
  `TYP_ACTION_TYPE` INT NOT NULL,
  `CMT_COMMENT` INT NULL DEFAULT NULL,
  PRIMARY KEY (`ACT_ID`),
  INDEX `fk_ACTION_FLOW_TICKET1_idx` (`TCK_TICKET` ASC),
  INDEX `fk_ACTION_FLOW_USER1_idx` (`USR_CHANGE_AUTHOR` ASC),
  INDEX `fk_ACTION_FLOW_ACTION_TYPE1_idx` (`TYP_ACTION_TYPE` ASC),
  INDEX `fk_ACTION_FLOW_COMMENT1_idx` (`CMT_COMMENT` ASC),
  CONSTRAINT `fk_ACTION_FLOW_TICKET1`
    FOREIGN KEY (`TCK_TICKET`)
    REFERENCES `TICKET_SYSTEM`.`TICKET` (`TCK_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ACTION_FLOW_USER1`
    FOREIGN KEY (`USR_CHANGE_AUTHOR`)
    REFERENCES `TICKET_SYSTEM`.`USER` (`USR_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ACTION_FLOW_ACTION_TYPE1`
    FOREIGN KEY (`TYP_ACTION_TYPE`)
    REFERENCES `TICKET_SYSTEM`.`ACTION_TYPE` (`TYP_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ACTION_FLOW_COMMENT1`
    FOREIGN KEY (`CMT_COMMENT`)
    REFERENCES `TICKET_SYSTEM`.`COMMENT` (`CMT_ID`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;