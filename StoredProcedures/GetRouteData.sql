USE u544302174_GTBikeVRoutes;

DROP PROCEDURE IF EXISTS GetRouteData;

DELIMITER //

CREATE PROCEDURE GetRouteData(IN vipv4 CHAR(40), IN vRouteName VARCHAR(255))
BEGIN

	SELECT
        rt.RouteName,
        rt.Author,
        rt.Map,
        rt.Type,
        rt.DistanceKM,
        rt.DistanceMI,
        rt.ElevationM,
        rt.ElevationFT,
        rt.Description,
        rt.UploadDateTime,
        rt.UpdateDateTime,
		max(this.Rating) as UserRating,
        count(distinct src.SubmitterID)  as RatingCount,
        round(avg(src.Rating)*2,0)/2 AS CurrentRating
	FROM
		u544302174_GTBikeVRoutes.Routes rt
		LEFT JOIN u544302174_GTBikeVRoutes.UserCourseLastRatingView src
			ON rt.RouteKey = src.RouteKey
		LEFT JOIN u544302174_GTBikeVRoutes.UserCourseLastRatingView this
			ON rt.RouteKey = this.RouteKey
			AND src.SubmitterID = this.SubmitterID
			AND this.SubmitterID = SHA2(vipv4, 256)
	WHERE
		(
			vRouteName = 'ALL'
            OR vRouteName = rt.RouteName 
		)
    GROUP BY
        rt.RouteName,
        rt.Author,
        rt.Map,
        rt.Type,
        rt.DistanceKM,
        rt.DistanceMI,
        rt.ElevationM,
        rt.ElevationFT,
        rt.Description,
        rt.UploadDateTime,
        rt.UpdateDateTime;

END //
DELIMITER ;


/*
CALL GetRouteData('104.152.107.91','the_tourist');
CALL GetRouteData('104.152.107.91','ALL');
CALL GetRouteData('104.152.107.91','the_tourist')
*/
