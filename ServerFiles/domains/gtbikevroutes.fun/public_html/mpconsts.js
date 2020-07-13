
		// set up a bunch of constant definitions at the outset to simplify 
		// adjusting if needed.
		// first set define aspects of the map style buttons.
		const TEXT_OFFSET_X = 12; // how far all button text is offset from top
		const TEXT_OFFSET_Y = 14; // how far all button text is offset from left
		// details for buttons
		const LINK_1_TEXT = "Atlas"; // text
		const LINK_1_X = 1216; // position
		const LINK_1_Y = 0;
		const LINK_1_HEIGHT = 21; // size
		const link1Width = 63;
		const LINK_2_TEXT = "Road";
		const LINK_2_X = 1216;
		const LINK_2_Y = 20;
		const LINK_2_HEIGHT = 21;
		const LINK_2_WIDTHh = 63;
		const LINK_3_TEXT = "Satellite";
		const LINK_3_X = 1216;
		const LINK_3_Y = 40;
		const LINK_3_HEIGHT = 21;
		const LINK_3_WIDTHh = 63;
		// set up params from the URL for use in the rest of the script
		const QUERYSTRING = window.location.search;
		const URLPARAMS = new URLSearchParams(QUERYSTRING);
		// These define conversion factors to convert from the .fit x/y lat/long to
		//  equivalent map pixels, based on 2048x2048 map images with 0,0 being top left 
		const XFACTOR = 17375; // this is multipler to convert to latlong to pixels
		const XOFFSET = 169.9250; // this is offset to convert latlong to to pixels
	// note that it's not quite the same as the offset in the ini, since game 0's from map center, html from top/left.
		const YFACTOR = -18275; // this is multipler to convert latlong to to pixels
		const YOFFSET = 19.0309	; // this is offset to convert latlong to to pixels
		// not sure why x/y are different, but they are ... maybe the map images are squished slightly?
		// sanity limits on values
		const X_HI_LIM = 2007; // the map has wide boundaries with no roads
		const X_LO_LIM = 41; // and limiting this makes some other stuff easier
		const Y_HI_LIM = 2007; // so we use 98% of 2048= 2007
		const Y_LO_LIM = 41; // and 2048-2007=41
		const Z_HI_LIM = 50000; // just sanity imposed here - you're not 50km in the air
		const Z_LO_LIM = -5000; // or 5km underground 
		// metric/imperial conversion factors.
		const METERS_2_FEET = 3.28084; // this is multipler for conversion to either imperial
		const KM_2_MI = 0.621371; // this is multipler for conversion to either imperial
		const EARTH_RADIUS = 6371; // in km
		const AX_MX_LABEL_X = 2; // pixels from left border for maximum elevation label on z axis
		const AX_MX_LABEL_Y = 10; //  pixels from top border for maximum elevation label on z axis
		const AX_MN_LABEL_X = 2; // pixels from left border for minimum elevation label on z axis
		const AX_MN_LABEL_Y = 60; // pixels from top border for minimum elevation label on z axis
		const AX_CT_LABEL_X = 2;
		const AX_CT_LABEL_Y = 35;
		const AX_LABEL_X = 610;
		const AX_LABEL_Y = 10;
		const ELV_DIST_LABEL_X = 1130;
		const ELV_DIST_LABEL_Y = 15;
		const ELV_TIME_LABEL_X = 1130;
		const ELV_TIME_LABEL_Y = 30;
		const ELV_ASCE_LABEL_X = 1130;
		const ELV_ASCE_LABEL_Y = 45;
		const ELV_DESC_LABEL_X = 1130;
		const ELV_DESC_LABEL_Y = 60;
		const DEFAULT_MAP_TYPE = "atlas"; // just setting a default
		const DEFAULT_MET = "Metric"; // just setting a default
		const DEFAULT_ELEX = "d";
		const ATLAS_PNG = 'images/map_atls.png'; // file source for atlas map
		const ROAD_PNG = 'images/map_road.png'; // file source for road map
		const SATL_PNG = 'images/map_satl.png'; // file source for satellite map
		/// COLORS!!! woo.  Lots of colors for canvas elements defined here.
		const ATLAS_BG = "#0fa8d2"; // background color for atlas map
		const ROAD_BG = "#1862ad"; // background color for road map
		const SATL_BG = "#143d6b"; // background color for satellite map
		const ATLAS_LN = "#0000ff"; // line color for atlas map
		const ROAD_LN = "#ff0000"; // line color for road map
		const SATL_LN = "#ff00ff"; // line color for satellite map
		const BTN_ATLAS_COLOR = '#808923';//'rgb(128,185,35)'; // color of bg for atlas
		const BTN_ROAD_COLOR = '#92D2BB';//'rgb(146, 210, 187)'; // color of bg for road
		const BTN_SATL_COLOR = '#009900'; // color of bg for sat
		const AXIS_COLOR = "#ffffff"; // color of the axis labels 
		const ELV_LN_COLOR = "#D0D0D0"; // color of the line 
		const HRM_LN_COLOR = "#20FF20"; //
		const CAD_LN_COLOR = "#6060FF"; //
		const PWR_LN_COLOR = "#FF2020"; //
		const ELV_FL_COLOR = "#606060"; // color of the graph fill 
		const HRM_FL_COLOR = "#006000"; // 
		const CAD_FL_COLOR = "#0040A0"; //
		const PWR_FL_COLOR = "#600000"; //
		const ELV_BG_COLOR = "#303030"; // color of the background in elevation chart
		const HRM_BG_COLOR = "#003000"; // 
		const CAD_BG_COLOR = "#002050"; //
		const PWR_BG_COLOR = "#300000"; //

		const VISIBLE_BODY_BG = '#A0A0A0';
		const MIN_LN_WIDTH = 0.25;
		const ANI_DOT_R = 5;
		const ANIMATED_FRAMES = 243; // number of frames to display in animation
		const INITIAL_ZOOM = 0.42;
		const INITIAL_TRANSLATION_X = 180;
		const INITIAL_TRANSLATION_Y = -120;
	