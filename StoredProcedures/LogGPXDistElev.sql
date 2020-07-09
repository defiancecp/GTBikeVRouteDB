USE u544302174_GTBikeVRoutes;

DROP PROCEDURE IF EXISTS LogGPXDistElev;

DELIMITER //

CREATE PROCEDURE LogGPXDistElev(IN vRouteName VARCHAR(255),vDistanceKM decimal(10,2),vAscentM decimal(10,1),vDescentM decimal(10,1))
BEGIN

	INSERT INTO GPXLogs (RouteName,DistanceKM,AscentM,DescentM) 
	VALUES(vRouteName,vDistanceKM,vAscentM,vDescentM);
    
    UPDATE GPXLogs
    SET RouteKey = (
		SELECT MAX(RouteKey) FROM Routes WHERE RouteName = vRouteName
		)
    WHERE RouteKey IS NULL;

	DELETE FROM GPXLogs WHERE SubmissionDateTime <= DATE_SUB(now(), INTERVAL 6 MONTH);

END //
DELIMITER ;


/*
CALL LogGPXDistElev('alamo_sea',100,28,27);
*/

