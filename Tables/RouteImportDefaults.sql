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
	UNIQUE KEY RouteName (RouteName)
);

INSERT INTO RouteImportDefaults(
	RouteName,
	Author,
	Map,
	`Type`,
	DistKM,
	DistMI,
	ElevM,
	ElevFT,
	`Description`,
	UploadDateTime
    )
SELECT
	'alamo_sea',
    'Nestor Matas (Makinolo)',
    '',
    'Gravel',
    8.9,
    5.5,
	14,
    45,
    'One of the routes included in the GTBikeV mod by default',
    NOW()
UNION ALL SELECT
	'los_santos_hills',
    'Nestor Matas (Makinolo)',
    '',
    'Road',
    15.9,
    9.9,
	126,
    415,
    'One of the routes included in the GTBikeV mod by default',
    NOW()
UNION ALL SELECT
	'Palomino_Highlands',
    '(UNKNOWNN CONTRIBUTOR)',
    '',
    'Road',
    0,
    0,
	0,
    0,
    'One of the routes included in the GTBikeV mod by default',
    NOW()
UNION ALL SELECT
	'Suburb_Crit',
    'Nestor Matas (Makinolo)',
    '',
    'Road',
    0,
    0,
	0,
    0,
    'One of the routes included in the GTBikeV mod by default',
    NOW()
UNION ALL SELECT
	'tour_los_santos',
    'Nestor Matas (Makinolo)',
    '',
    'Road',
    30.1,
    18.7,
	22,
    73,
    'One of the routes included in the GTBikeV mod by default',
    NOW()




/*
SELECT * FROM RouteImportDefaults;
*/
