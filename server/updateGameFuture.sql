CREATE DEFINER=`fcjuarez_sysadm`@`%` PROCEDURE `UpdateGameFuture`(
	IN `pGameFutureId` INT,
	IN `pInitialSeasonId` INT,
	IN `pInitialTournamentId` INT,
	IN `pInitialWeek` INT,
	IN `pInitialDate` DATETIME,
	IN `pInitialHour` TIME,
	IN `pInitialTitle` VARCHAR(300),
	IN `pInitialSubtitle` VARCHAR(300),
	IN `pInitialDescription` TEXT,
	IN `pInitialVersusTeam` VARCHAR(100),
	IN `pInitialVersusTeamAtHome` BOOLEAN,
	IN `pInitialStadium` VARCHAR(50),
	IN `pInitialCity` VARCHAR(50),
	IN `pInitialAddress` VARCHAR(100),
	IN `pInitialLinkAddress1` VARCHAR(300),
	IN `pInitialLinkAddress2` VARCHAR(300),
	IN `pInitialActive` BOOLEAN,
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
  IN `pPn1` VARCHAR(50),
  IN `pPn2` VARCHAR(50),
	IN `pLastUpdateBy` INT,
	OUT `pReturnValue` INT,
	OUT `pReturnMessage` VARCHAR(200)


)
SP:BEGIN


	IF (
	SELECT
		COUNT(*)
	FROM
		GameFuture
	WHERE
		GameFutureId = pGameFutureId) = 0 THEN

		SET pReturnValue = -1;
		SET pReturnMessage = "Record deleted!";
		LEAVE SP;

	END IF;


	IF (
	SELECT
		COUNT(*)
	FROM
		GameFuture
	WHERE
		GameFutureId = pGameFutureId
		AND (SeasonId <> pInitialSeasonId
        OR TournamentId <> pInitialTournamentId
        OR Week <> pInitialWeek
        OR `Date` <> pInitialDate
        OR `Hour` <> pInitialHour
        OR Title <> pInitialTitle
        OR Subtitle <> pInitialSubtitle
		#OR Description <> pInitialDescription
        OR VersusTeam <> pInitialVersusTeam
        OR VersusTeamAtHome <> pInitialVersusTeamAtHome
        OR Stadium <> pInitialStadium
        OR City <> pInitialCity
        OR Address <> pInitialAddress
        OR LinkAddress1 <> pInitialLinkAddress1
        OR LinkAddress2 <> pInitialLinkAddress2
		OR Active <> pInitialActive) ) > 0 THEN

		SET pReturnValue = -2;
		SET pReturnMessage = "Record updated!";
		LEAVE SP;

	END IF;


	SET AUTOCOMMIT=0;

	START TRANSACTION;

	UPDATE
		GameFuture
	SET
		SeasonId = pSeasonId,
        TournamentId = pTournamentId,
        Week = pWeek,
        `Date` = pDate,
        `Hour` = pHour,
		Title = pTitle,
        Subtitle = pSubtitle,
		Description = pDescription,
        VersusTeam = pVersusTeam,
        VersusTeamAtHome = pVersusTeamAtHome,
        Stadium = pStadium,
        City = pCity,
        Address = pAddress,
        LinkAddress1 = pLinkAddress1,
        LinkAddress2 = pLinkAddress2,
		Active = pActive,
        pnId1 = pPn1,
        pnId2 = pPn2,
		LastUpdateBy = pLastUpdateBy,
		LastUpdateDate = NOW()
	WHERE
		GameFutureId = pGameFutureId;

	IF ROW_COUNT() > 1 THEN
		ROLLBACK;
		SET pReturnValue = -3;
		SET pReturnMessage = "Error while trying to update record!";
		LEAVE SP;
	ELSE
		COMMIT;
	END IF;

	SET pReturnValue = 0;
	SET pReturnMessage = "Record updated!";


END