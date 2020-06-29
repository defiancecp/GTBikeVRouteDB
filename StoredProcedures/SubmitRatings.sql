USE u544302174_GTBikeVRoutes;

DROP PROCEDURE IF EXISTS SubmitRatings;

DELIMITER //

CREATE PROCEDURE SubmitRatings(IN vipv4 CHAR(20), IN vRouteName VARCHAR(255), IN vRating DECIMAL(4,2))
BEGIN

	INSERT INTO Ratings(RouteKey,SubmitterID,Rating,SubmissionDateTime) 
    SELECT
    	r.RouteKey,
        SHA2(vipv4, 256) as SubmitterID,
        vRating,
        Now()
	FROM
		Routes r
	WHERE
		r.RouteName = vRouteName;


END //
DELIMITER ;


/*
CALL SubmitRating('123.123.123.123','the_tourist',2.5);
*/
