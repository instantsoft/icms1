/*
// JoomlaWorks "Tabs & Slides" Plugin for Joomla! 1.0.x - Version 2.3
// License: http://www.gnu.org/copyleft/gpl.html
// Authors: Fotis Evangelou - George Chouliaras
// Copyright (c) 2006 - 2007 JoomlaWorks.gr - http://www.joomlaworks.gr
// Project page at http://www.joomlaworks.gr - Demos at http://demo.joomlaworks.gr
// Support forum at http://forum.joomlaworks.gr
// ***Last update: August 30th, 2007***
*/

// DOM Loader
var DomLoaded =
{
	onload: [],
	loaded: function()
	{
		if (arguments.callee.done) return;
		arguments.callee.done = true;
		for (i = 0;i < DomLoaded.onload.length;i++) DomLoaded.onload[i]();
	},
	load: function(fireThis)
	{
		this.onload.push(fireThis);
		if (document.addEventListener) 
			document.addEventListener("DOMContentLoaded", DomLoaded.loaded, null);
		if (/KHTML|WebKit/i.test(navigator.userAgent))
		{ 
			var _timer = setInterval(function()
			{
				if (/loaded|complete/.test(document.readyState))
				{
					clearInterval(_timer);
					delete _timer;
					DomLoaded.loaded();
				}
			}, 10);
		}
		/*@cc_on @*/
		/*@if (@_win32)
		var proto = "src='javascript:void(0)'";
		if (location.protocol == "https:") proto = "src=//0";
		document.write("<scr"+"ipt id=__ie_onload defer " + proto + "><\/scr"+"ipt>");
		var script = document.getElementById("__ie_onload");
		script.onreadystatechange = function() {
		    if (this.readyState == "complete") {
		        DomLoaded.loaded();
		    }
		};
		/*@end @*/
	   window.onload = DomLoaded.loaded;
	}
};

// Load everything up!
DomLoaded.load(function() {
	tabberAutomatic(tabberOptions);	// Load the tabs
	initShowHideDivs();				// Load the slides
	//showHideContent(false,1);});	// Automatically expand first item - disabled by default
});