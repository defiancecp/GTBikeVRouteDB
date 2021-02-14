USE u544302174_GTBikeVRoutes;

DROP TABLE IF EXISTS Routes;

CREATE TABLE Routes (
	RouteKey int(11) NOT NULL AUTO_INCREMENT,
	RouteName varchar(255) NOT NULL,
	Author varchar(255) NOT NULL,
	Map varchar(255) DEFAULT NULL,
	Type varchar(255) NOT NULL,
	DistanceKM decimal(10,2) NOT NULL,
	DistanceMI decimal(10,2) NOT NULL,
	ElevationM decimal(10,1) NOT NULL,
	ElevationFT decimal(10,1) NOT NULL,
	`Description` varchar(4000) NOT NULL,
	UploadDateTime datetime NOT NULL DEFAULT current_timestamp(),
	UpdateDateTime datetime NOT NULL DEFAULT current_timestamp(),
	Active int(11) NOT NULL DEFAULT 1,
    RouteDisplayName varchar(255) NOT NULL,
	PRIMARY KEY (RouteKey),
	UNIQUE KEY RouteName (RouteName)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/*
SELECT * FROM Routes;
*/
