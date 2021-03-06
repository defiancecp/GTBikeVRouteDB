USE u544302174_GTBikeVRoutes;

-- This table intended to provide basic metadata 
-- for mod-included routes.
-- metadata is manually maintained for now.

DROP TABLE IF EXISTS RouteImportDefaults;

CREATE TABLE RouteImportDefaults (
	RouteName varchar(255) NOT NULL,
	Author varchar(255) NOT NULL,
	Map varchar(255) DEFAULT NULL,
	`Type` varchar(255) NOT NULL,
	DistKM decimal(10,2) NOT NULL,
	DistMI decimal(10,2) NOT NULL,
	ElevM decimal(10,1) NOT NULL,
	ElevFT decimal(10,1) NOT NULL,
	`Description` varchar(4000) NOT NULL,
	UploadDateTime datetime NOT NULL,
    RouteDisplayName varchar(255) NOT NULL,
	UNIQUE KEY RouteName (RouteName)
);



/*
SELECT * FROM RouteImportDefaults;
*/
