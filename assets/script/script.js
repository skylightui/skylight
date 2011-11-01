// DEFAULT --------------------------------------------------------------
function InitDefault(){
	
	app.init();
	
	// DEV ONLY: set reference grid
	if( $('.canvas').length ) $(this).griddit({vertical: 18});
	
}

// APP OBJECT ===================================================================

var app = function () {
	
	var	deviceAgent 										= navigator.userAgent.toLowerCase(),				// check for iOs device
		agentID 											= deviceAgent.match(/(iphone|ipod|ipad)/),
		bol_isIos											= (agentID) ? true : false,
		bol_isIphone										= (deviceAgent.match(/(iphone)/)) ? true : false;
	
	function init() {
		
		ui.init();
		events.init();
		
	}
	
	return { init: init, bol_isIos: bol_isIos, bol_isIphone: bol_isIphone }
	
}();

// UI OBJECT ===================================================================

var ui = function () {
	
	function init() {
		
	}
	
	return {init: init}
	
}();

// EVENTS OBJECT ===================================================================

var events = function () {	
	
	var keyboardEvents = function() {
		
	}(),
	
	mouseEvents = function() {
		
		bind = function () {
			
			if( !app.bol_isIos ) {
				$(window).scroll(onScroll);
			}
			
		}
		
		onScroll = function() {
			
			// code to be run when the user scrolls the window
			
		}
		
		return {bind: bind}
		
	}()
	
	function init() {
		mouseEvents.bind();
	}
	
	return {
		init: init, mouseEvents: mouseEvents
	}
	
}();