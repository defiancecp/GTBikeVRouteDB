USE u544302174_GTBikeVRoutes;

DROP TABLE IF EXISTS RouteImportStaging;

CREATE TABLE RouteImportStaging (
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
	UNIQUE KEY RouteName (RouteName)
);


/*
SELECT * FROM RouteImportStaging;
*/