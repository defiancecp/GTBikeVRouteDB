USE u544302174_GTBikeVRoutes;

DROP TABLE IF EXISTS Ratings;

CREATE TABLE Ratings (
	SubmitterID varchar(255) NOT NULL,
	RouteKey int(11) NOT NULL,
	SubmissionDateTime datetime NOT NULL DEFAULT current_timestamp(),
	Rating decimal(4,1) DEFAULT NULL,
	KEY ix_Ratings_RouteKey (RouteKey),
	KEY ix_Ratings_SubmitterID (SubmitterID),
	KEY ix_Ratings_SubmissionDateTime (SubmissionDateTime),
	CONSTRAINT Ratings_ibfk_1 FOREIGN KEY (RouteKey) REFERENCES Routes (RouteKey) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/*
SELECT * FROM Ratings;
*/
