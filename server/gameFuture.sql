CREATE TABLE `GameFuture` (
	`GameFutureId` INT(10) UNSIGNED NOT NULL,
	`SeasonId` INT(10) UNSIGNED NOT NULL,
	`TournamentId` INT(10) UNSIGNED NOT NULL,
	`Week` INT(11) NOT NULL,
	`Date` DATE NOT NULL,
	`Hour` TIME NOT NULL,
	`Title` VARCHAR(300) NOT NULL COLLATE 'utf8_unicode_ci',
	`Subtitle` VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Description` TEXT NULL COLLATE 'utf8_unicode_ci',
	`VersusTeam` VARCHAR(100) NOT NULL COLLATE 'utf8_unicode_ci',
	`VersusTeamAtHome` TINYINT(1) NOT NULL,
	`Stadium` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`City` VARCHAR(50) NOT NULL COLLATE 'utf8_unicode_ci',
	`Address` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`LinkAddress1` VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`LinkAddress2` VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`Active` TINYINT(1) NOT NULL,
	`InputDate` DATETIME NOT NULL,
	`LastUpdateBy` INT(10) UNSIGNED NOT NULL,
	`LastUpdateDate` DATETIME NOT NULL,
	`pnId1` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	`pnId2` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci',
	PRIMARY KEY (`GameFutureId`),
	INDEX `FKGameFuture01_idx` (`SeasonId`),
	INDEX `FKGameFuture02_idx` (`LastUpdateBy`),
	CONSTRAINT `FKGameFuture01` FOREIGN KEY (`SeasonId`) REFERENCES `Season` (`SeasonId`),
	CONSTRAINT `FKGameFuture02` FOREIGN KEY (`LastUpdateBy`) REFERENCES `User` (`UserId`)
)
COLLATE='utf8_unicode_ci'
ENGINE=InnoDB
;
