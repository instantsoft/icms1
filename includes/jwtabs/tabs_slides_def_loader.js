/*
// JoomlaWorks "Tabs & Slides" Plugin for Joomla! 1.0.x - Version 2.3
// License: http://www.gnu.org/copyleft/gpl.html
// Authors: Fotis Evangelou - George Chouliaras
// Copyright (c) 2006 - 2007 JoomlaWorks.gr - http://www.joomlaworks.gr
// Project page at http://www.joomlaworks.gr - Demos at http://demo.joomlaworks.gr
// Support forum at http://forum.joomlaworks.gr
// ***Last update: August 30th, 2007***
*/

// Default Loader
function init_jwTS() {
    if (arguments.callee.done) return;
    arguments.callee.done = true;
	initShowHideDivs();
	tabberAutomatic(tabberOptions);
	//showHideContent(false,1);});	// Automatically expand first item - disabled by default
};
// DOM2
if ( typeof window.addEventListener != "undefined" ) {
	window.addEventListener( "load", init_jwTS, false );
// IE 
} else if ( typeof window.attachEvent != "undefined" ) {
	window.attachEvent( "onload", init_jwTS );
} else {
	if ( window.onload != null ) {
		var oldOnload = window.onload;
		window.onload = function ( e ) {
			oldOnload( e );
			init_jwTS();
		};
	} else {
		window.onload = init_jwTS;
	}
}