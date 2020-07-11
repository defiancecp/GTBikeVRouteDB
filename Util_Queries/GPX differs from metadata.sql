use u544302174_GTBikeVRoutes;

-- Possible future enhancement: Could search for metric/imperial mismatches   
WITH agGPXLogs AS (
	SELECT
		gl.RouteKey,
        count(1) as SubmissionCount,
        max(gl.SubmissionDateTime) as LastSubmission,
		Avg(gl.DistanceKM) avgDistanceKM,
		Max(gl.DistanceKM) maxDistanceKM,
		Min(gl.DistanceKM) minDistanceKM,
		STD(gl.DistanceKM) stdDistanceKM,
		Avg(gl.AscentM) avgElevM,
		Min(gl.AscentM) minElevM,
		Max(gl.AscentM) maxElevM,
		STD(gl.AscentM) stdElevM
	FROM
		GPXLogs gl
	WHERE
		AscentM > 5
		AND DistanceKM > 1
	-- might eventually update this to limit to recent using submissiondatetime?
	GROUP BY
		RouteKey
)
SELECT
	r.RouteName,
	abs(gl.avgDistanceKM - r.DistanceKM)  as DistDeviation,
	abs(gl.avgElevM - r.ElevationM)  as ElevDeviation,
    gl.LastSubmission,

    r.DistanceKM as ReportedDistanceKM,
    gl.avgDistanceKM as GPXDistanceKM,
    gl.stdDistanceKM as GPXDistanceKM_StdDeviation,

    r.ElevationM as ReportedElevationM,
    gl.avgElevM as GPXElevationM,
    gl.stdElevM as GPXElevationM_StdDeviation

FROM
	Routes r
	INNER JOIN agGPXLogs gl
		ON r.RouteKey = gl.RouteKey
ORDER BY
	-- abs(gl.avgDistanceKM - r.DistanceKM) desc -- switch to this to focus on distance deviations
	abs(gl.avgElevM - r.ElevationM) desc -- switch to this to focus on elevation deviations
	
