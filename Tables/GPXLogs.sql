USE u544302174_GTBikeVRoutes;

DROP TABLE IF EXISTS GPXLogs;

CREATE TABLE GPXLogs (
	SubmissionDateTime datetime NOT NULL DEFAULT current_timestamp(),
	RouteName varchar(255) NOT NULL,
    RouteKey int(11),
    DistanceKM decimal(10,2),
    AscentM decimal(10,1),
    DescentM decimal(10,1)
)

/*
SELECT * FROM GPXLogs;
*/
