USE u544302174_GTBikeVRoutes;

DROP VIEW IF EXISTS CourseRatingView;

CREATE VIEW CourseRatingView AS
SELECT
	src.RouteKey AS RouteKey,
    ifnull(max(this.Rating),avg(src.Rating)) AS Rating
FROM
	u544302174_GTBikeVRoutes.UserCourseLastRatingView src
    LEFT JOIN u544302174_GTBikeVRoutes.UserCourseLastRatingView this
		ON src.RouteKey = this.RouteKey
        AND src.SubmitterID = this.SubmitterID
        AND this.SubmitterID = 'eventually sha2 ip'
GROUP BY
	src.RouteKey;


/*
SELECT * FROM CourseRatingView;
*/
