USE u544302174_GTBikeVRoutes;

DROP PROCEDURE IF EXISTS ProcessImportedRoutes;

DELIMITER //

CREATE PROCEDURE ProcessImportedRoutes()
BEGIN

    UPDATE
        Routes Main
        INNER JOIN (SELECT * FROM RouteImportStaging UNION ALL SELECT * FROM RouteImportDefaults) Incoming
            ON Main.RouteName = Incoming.RouteName
    SET
        Main.RouteDisplayName = Incoming.RouteDisplayName,
        Main.Author = Incoming.Author,
        Main.Description = Incoming.Description,
        Main.DistanceKM = Incoming.DistKM,
        /*Main.DistanceMI = Incoming.DistMI,*/
        Main.DistanceMI = Incoming.DistKM*0.621371, /* ensure alignment, rely on metric */
        Main.ElevationM = Incoming.ElevM,
        /*Main.ElevationFT = Incoming.ElevFT,*/
        Main.ElevationFT = Incoming.ElevM*3.28084, /* ensure alignment, rely on metric */
        Main.Map = Incoming.Map,
        Main.Type = Incoming.Type,
        Main.UpdateDateTime = NOW(),
		Main.Active = 1
	WHERE
        Main.Author <> Incoming.Author
        OR Main.RouteDisplayName <> Incoming.RouteDisplayName
        OR Main.Description <> Incoming.Description
        OR Main.DistanceKM <> Incoming.DistKM
        OR Main.DistanceMI <> Incoming.DistMI
        OR Main.ElevationFT <> Incoming.ElevFT
        OR Main.ElevationM <> Incoming.ElevM
        OR Main.Map <> Incoming.Map
        OR Main.Type <> Incoming.Type;

    UPDATE
        Routes Main
        LEFT JOIN (SELECT * FROM RouteImportStaging UNION ALL SELECT * FROM RouteImportDefaults) Incoming
            ON Main.RouteName = Incoming.RouteName
    SET
        Main.Active = 0
	WHERE
		Incoming.RouteName IS NULL;


    INSERT INTO Routes (
        RouteName,
        RouteDisplayName,
        Author,
        Description,
        DistanceKM,
        DistanceMI,
        ElevationFT,
        ElevationM,
        Map,
        Type,
        Active
        )
    SELECT
        NewData.RouteName,
        NewData.RouteDisplayName,
        NewData.Author,
        NewData.Description,
        NewData.DistKM,
        NewData.DistMI,
        NewData.ElevFT,
        NewData.ElevM,
        NewData.Map,
        NewData.Type,
        1
    FROM
        (SELECT * FROM RouteImportStaging UNION ALL SELECT * FROM RouteImportDefaults) NewData
        LEFT JOIN Routes OldData
            ON NewData.RouteName = OldData.RouteName
    WHERE
        OldData.RouteName IS NULL;

END //
DELIMITER ;


/*
CALL ProcessImportedRoutes();
*/

