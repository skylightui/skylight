
// usage: log('inside coolFunc', this, arguments);
// paulirish.com/2009/log-a-lightweight-wrapper-for-consolelog/
window.log = function(){
  log.history = log.history || [];   // store logs to an array for reference
  log.history.push(arguments);
  arguments.callee = arguments.callee.caller;  
  if(this.console) console.log( Array.prototype.slice.call(arguments) );
};
// make it safe to use console.log always
(function(b){function c(){}for(var d="assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn".split(","),a;a=d.pop();)b[a]=b[a]||c})(window.console=window.console||{});


// place any jQuery/helper plugins in here, instead of separate, slower script files.

// UTILS --------------------------------------------------------------
(function($) {
jQuery.fn.griddit = function(options){
	/*
	Purpose: uses Canvas to draw a reference grid over the page, toggled with the ESC key
	usage: $(this).griddit({options});
	*/
	
	// defaults ===========================
	var defaults = {  
		width: 940,
		height: Math.max($(window).height(), $('html').height()),
		cols: 12,
		gutterwidth: 20,
		colColour: "rgba(255,0,0,0.2)",
		vertical: 20
	};
	var options = $.extend(defaults, options);
	
	var left		= ($(window).width() / 2) - (defaults.width / 2);
	var html 		= '<canvas id="grid-cols" width="' + defaults.width + '" height="' + defaults.height + '" style="position: absolute; top: 0; left: ' + left + 'px"></canvas>';
	$('body').append(html);
	var canvas 		= document.getElementById("grid-cols");
	var ctx 		= canvas.getContext("2d");
	
	// vertical grid columns ===========================
	var colour;
	var col_w		= ( defaults.width - ((defaults.cols - 1) * defaults.gutterwidth) ) / defaults.cols;
	if( window.console && console.log ) console.log("GRIDDIT column width is " + col_w + "px with a gutterwidth of " + defaults.gutterwidth);
	if( (col_w % 1) == 0) {
		for (var i = 0; i < defaults.cols; i++) {
			colour 							= defaults.colColour;
			if(colour == 'random') colour 	= 'rgba(' + RandRange(0, 255) + ',' + RandRange(0, 255) + ',' + RandRange(0, 255) + ',' + '0.2)';
			ctx.fillStyle 					= colour;
			ctx.fillRect (i * (col_w + defaults.gutterwidth), 0, col_w, defaults.height);
		};
	}else{
		alert('Your grid isn\'t a nice round number :/');
	}
	
	// horizontal grid lines ===========================
	ctx.strokeStyle 	= colour;
	ctx.lineWidth 		= 1;
	ctx.beginPath();
	ctx.beginPath();
	for (var i = 0; i < (defaults.height / defaults.vertical); i++) {
		var y = (i * defaults.vertical) + 0.5;
		ctx.moveTo(0, y);
		ctx.lineTo(defaults.width, y);
	};
	ctx.stroke();
	
	$(canvas).hide();
	
	$('html').keyup(function(event) {
		if(event.keyCode == 27){
			if( $(canvas).is(':visible') ){
				$(canvas).hide();
			}else{
				$(canvas).show();
			}
		}
	});
	
}
})(jQuery);
  	
(function($) {
jQuery.fn.eqByRow = function(){
	/*
	Purpose: equalise the heights of li's in a list by row
	usage: $('#eq-this-list').eqByRow();
	*/
	var inRow 	= Math.floor( $(this).width() / $(this).children("li:first").width() );
	var items 	= $(this).children('li');
	for (var i = 0; i < ($(items).length / inRow); i++){
		var max 	= 0;
		var start 	= (i * inRow);
		for (var j = start; j < start + inRow; j++){
			if(items[j]){
				if( $($(items)[j]).height() > max ) max = $($(items)[j]).height();
			}
		};
		for (var k = start; k < start + inRow; k++){
			if(items[k]){
				$($(items)[k]).height(max);
			}
		};
	};
}
})(jQuery);

(function($) {
jQuery.fn.biggerClick = function(){
	/*
	Purpose: makes a container 'hot', and binds it click event to go to the location of the first <a>'s href it finds within it
	usage: $('#make-these-hot ul li').biggerClick();
	*/
	$(this).hover(
		function(){
			$(this).addClass("hover");
		},function(){
			$(this).removeClass("hover");
		}
	)
	$(this).click(function(){
		window.location = $(this).find("a:first").attr("href");
	})
}
})(jQuery);

(function($) {
jQuery.fn.defaultValueInput = function(){
	for (var i=0; i < $(this).length; i++) {
		$(this[i]).data('defaultValue', $(this[i]).attr('value'));
		$(this[i]).addClass('default-value');
	};
	$(this).focus(function(){
		if( $(this).attr('value') == $(this).data('defaultValue') ){
			$(this).attr('value', '');
			$(this).removeClass('default-value');
		}
	});
	$(this).blur(function(){
		if( $(this).attr('value') == '' || $(this).attr('value') == $(this).data('defaultValue') ){
			$(this).attr('value', $(this).data('defaultValue'));
			$(this).addClass('default-value');
		}
	});
}
})(jQuery);

function SetExternalLinks(){
	/*
	Purpose: checks all <a>'s in the doc, if it has a rel attribute of 'external', sets to open in new window
	usage: <a href="http://www.google.com" rel="external">Google</a>
	*/
	$('a[rel=external]').click(function(){ window.open(this.href); return false; });
}

function RandRange(minNum, maxNum) {
	return (Math.floor(Math.random() * (maxNum - minNum + 1)) + minNum);
}

// ======================================================================
