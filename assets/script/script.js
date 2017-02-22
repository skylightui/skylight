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


$(document).ready(function(){




$("#main-image").load(function() {

        // set metadata width according to main-image width
        $("#left-metadata").css({"width": 640 - $('#main-image').width()});

        // if the main image's height is taller than the metadata section...
        if ($('#main-image').height() > $('#left-metadata').height()) {
            // resize the height to match
            $('#main-image').css({"height": $('#left-metadata').height() + 10});
            // resize metadata width accordingly
            $("#left-metadata").css({"width": 640 - $('#main-image').width()});
        }

        // if there is room for a second image
        if (parseInt($('#main-image').css('height')) + 180 < parseInt($('#left-metadata').css('height'))) {
            // stop it from being sized like a thumbnail
            $('#second-image').removeClass("record-image");
            // set it's width to the same as the main-image
            $('#second-image').css({"width": $('#main-image').width()});
        }

        // now check if the height is greater than the space we have left alongside the metadata
        if ($('#second-image').height() > ( $('#left-metadata').height() - $('#main-image').height() + 10 )) {

            // if there is less than 100 pixels of height
            if (($('#left-metadata').height() - $('#main-image').height()) < 100 ) {
                // make it a thumbnail
                $('#second-image').addClass("record-image");
                // remove the width set above
                $('#second-image').css({"width": ""});
                // push it below the metadata
                $('#second-image').css({"clear": "both"});
            }
            // there is enough space
            else {
                // set it's height to what's left
                $('#second-image').css({"height": $('#left-metadata').height() - $('#main-image').height() + 1});
                // remove it's set width
                $('#second-image').css({"width": ""});
            }

        }
        else {
            $('#second-image').css({"max-height": "500px"});
        }
    })

    $('.jcarousel')
        .jcarousel({
        // Configuration goes here
            wrap: 'circular'
        })
        .jcarouselAutoscroll({
            interval: 3000,
            target: '+=1',
            autostart: true
        });
    $('.jcarousel-control-prev')
        .jcarouselControl({
            target: '-=1'
        });
    $('.jcarousel-control-next')
        .jcarouselControl({
            target: '+=1'
        });

    $('audio[id^="audio-"]').bind("play", function(){
        ga('send', 'event', 'Audio', 'play', $(this).attr('title'));
    });

    // push the footer to the bottom for piccolo
    var docHeight = $(window).height();
    var footerHeight = $('.footer-piccolo').height();
    var footerTop = $('.footer-piccolo').position().top + footerHeight;
    var recaptchaHeight = 0;

    if($('#recaptcha_widget_div').length != 0) {
        recaptchaHeight = 112;
    }
    var footerMargin = docHeight - footerTop - recaptchaHeight;

    if (footerTop < (docHeight)) {
        $('.footer-margin').css('height', footerMargin + 'px');
    }

    var offset = $(':target').offset();
    var scrollto = offset.top - 100; // minus fixed header height
    $('html, body').animate({scrollTop:scrollto}, 0);

    var shiftWindow = function() { scrollBy(0, -500) };
    if (location.hash) shiftWindow();
    window.addEventListener("hashchange", shiftWindow);


});

// Javascript to enable link to tab
var hash = document.location.hash;
var prefix = "tab_";
if (hash) {
    $('.nav-tabs a[href='+hash.replace(prefix,"")+']').tab('show');
}

// Change hash for page-reload
$('.nav-tabs a').on('shown', function (e) {
    window.location.hash = e.target.hash.replace("#", "#" + prefix);
});
