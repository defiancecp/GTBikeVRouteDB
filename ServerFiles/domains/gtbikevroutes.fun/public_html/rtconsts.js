	const MIN_FILE_THRESHOLD = 200; 
	const DIST_OFFSET = 8;
	const ELEV_OFFSET = 10;
	const RATG_OFFSET = 7;
	// minimum number of bytes for valid file
	// cross-domain download requires client .js to pull into a blob and reconstruct the file.  This is critical because
	//  it means the file could possibly be created out of (for example) a 404 message.
	//  Intent of this threshold is to throw out bad results instead of giving them to the user as a file, with no indication
	//  that it's not a "real" file.
	const UNICODE_UP_ARROW = '\u2191';
	const UNICODE_DOWN_ARROW = '\u2193';
	const UNICODE_UPDOWN_ARROW = '\u21f5';
	const DEFAULT_MET = "Metric";
	const QUERY_STRING = window.location.search;
	const URL_PARAMS = new URLSearchParams(QUERY_STRING);
