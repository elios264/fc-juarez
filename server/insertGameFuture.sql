CREATE DEFINER=`fcjuarez_sysadm`@`%` PROCEDURE `InsertGameFuture`(
	IN `pSeasonId` INT,
	IN `pTournamentId` INT,
	IN `pWeek` INT,
	IN `pDate` DATETIME,
	IN `pHour` TIME,
	IN `pTitle` VARCHAR(300),
	IN `pSubtitle` VARCHAR(300),
	IN `pDescription` TEXT,
	IN `pVersusTeam` VARCHAR(100),
	IN `pVersusTeamAtHome` BOOLEAN,
	IN `pStadium` VARCHAR(50),
	IN `pCity` VARCHAR(50),
	IN `pAddress` VARCHAR(100),
	IN `pLinkAddress1` VARCHAR(300),
	IN `pLinkAddress2` VARCHAR(300),
	IN `pActive` BOOLEAN,
	IN `pLastUpdateBy` INT,
	OUT `pNextId` INT,
	OUT `pReturnValue` INT,
	OUT `pReturnMessage` VARCHAR(200)

,
	IN `pSeasonId` INT,
	IN `pTournamentId` INT,
	IN `pWeek` INT,
	IN `pDate` DATETIME,
	IN `pHour` TIME,
	IN `pTitle` VARCHAR(300),
	IN `pSubtitle` VARCHAR(300),
	IN `pDescription` TEXT,
	IN `pVersusTeam` VARCHAR(100),
	IN `pVersusTeamAtHome` BOOLEAN,
	IN `pStadium` VARCHAR(50),
	IN `pCity` VARCHAR(50),
	IN `pAddress` VARCHAR(100),
	IN `pLinkAddress1` VARCHAR(300),
	IN `pLinkAddress2` VARCHAR(300),
	IN `pActive` BOOLEAN,
	IN `pLastUpdateBy` INT,
	OUT `pNextId` INT,
	OUT `pReturnValue` INT,
	OUT `pReturnMessage` VARCHAR(200)


)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
SP:BEGIN

	SET AUTOCOMMIT=0;
	START TRANSACTION;

	SET @Id = (SELECT IFNULL(MAX(GameFutureId), 0) + 1 FROM GameFuture);


	INSERT INTO
		GameFuture
	SELECT
		@Id,
        pSeasonId,
        pTournamentId,
        pWeek,
        pDate,
        pHour,
		pTitle,
        pSubtitle,
		pDescription,
        pVersusTeam,
        pVersusTeamAtHome,
        pStadium,
        pCity,
        pAddress,
        pLinkAddress1,
        pLinkAddress2,
        pActive,
		NOW(),
		pLastUpdateBy,
		NOW();

	IF ROW_COUNT() <> 1 THEN
		ROLLBACK;
		SET pReturnValue = -1;
		SET pReturnMessage = "Error while trying to insert record!";
		LEAVE SP;
	END IF;

	COMMIT;

	SET pNextId = @Id;
	SET pReturnValue = 0;
	SET pReturnMessage = "Record inserted!";

END