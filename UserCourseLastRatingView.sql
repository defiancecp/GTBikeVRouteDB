USE u544302174_GTBikeVRoutes;

DROP VIEW IF EXISTS UserCourseLastRatingView;

CREATE VIEW UserCourseLastRatingView AS
SELECT
	rt.SubmitterID AS SubmitterID,
    rt.RouteKey AS RouteKey,
    rt.Rating AS Rating
FROM u544302174_GTBikeVRoutes.Ratings rt
	INNER JOIN(
			SELECT
				u544302174_GTBikeVRoutes.Ratings.SubmitterID AS SubmitterID,
				u544302174_GTBikeVRoutes.Ratings.RouteKey AS RouteKey,
				max(u544302174_GTBikeVRoutes.Ratings.SubmissionDateTime) AS SubmissionDateTimeMx
			FROM u544302174_GTBikeVRoutes.Ratings
			GROUP BY
				u544302174_GTBikeVRoutes.Ratings.SubmitterID,
				u544302174_GTBikeVRoutes.Ratings.RouteKey
            ) rmx
		ON rt.SubmissionDateTime = rmx.SubmissionDateTimeMx
        AND rt.RouteKey = rmx.RouteKey;


/*
SELECT * FROM UserCourseLastRatingView;
*/
