var spu_count = 0;
var spu_counter ='';
	function socialPopUP(options) {
		var defaults = { days_no_click : "10" };
		var options = jQuery.extend(defaults, options);
		window.options = options;
		
		var cook = readCookie('spushow');
		var waitCook = readCookie('spuwait');
		
		if (cook != 'true') {
			var windowWidth = document.documentElement.clientWidth;
			var windowHeight = document.documentElement.clientHeight;
			var popupHeight = jQuery("#spu-main").height();
			var popupWidth = jQuery("#spu-main").width();
			jQuery("#spu-main").css({
				"position": "fixed",
				"top": windowHeight / 2 - popupHeight / 2,
				"left": windowWidth / 2 - popupWidth / 2
			});
			jQuery("#spu-bg").css({
				"height": windowHeight
			});
			jQuery("#spu-bg").css({
				"opacity": defaults.opacity
			});
			jQuery("#spu-bg").fadeIn("slow");
			jQuery("#spu-main").fadeIn("slow");
		}
		
		if (defaults.advancedClose == true) {
			jQuery(document).keyup(function(e) {
				if (e.keyCode == 27) {
					spuFlush(defaults.days_no_click);
				}
			});
			var ua = navigator.userAgent,
			event = (ua.match(/iPad/i) || ua.match(/iPhone/i)) ? "touchstart" : "click";
			
			jQuery('body').on(event, function (ev) {
				
				spuFlush(defaults.days_no_click);
			});
			jQuery('#spu-main').click(function(event) {
				event.stopPropagation();
			});
		}
		if( parseInt(defaults.s_to_close) > 0 )
		{
			spu_count=defaults.s_to_close;
			spu_counter = setInterval(function(){spu_timer(defaults)}, 1000);
		}
		return true;
	}

function thanks_msg(options){

	if( options.thanks_msg){
		//I add some delay because twitter is not completing follow event
		setTimeout(function(){
			jQuery('#spu-msg-cont').hide().html(options.thanks_msg).fadeIn();
		}, 500);
	}
	setTimeout(function(){ spuFlush()}, 1000 * options.thanks_sec);
}


jQuery(document).ready(function(){
FB.Event.subscribe('edge.create', function(href) {
	clearInterval(spu_counter);
	thanks_msg(window.options);
});
if (typeof twttr !== 'undefined') {
	twttr.ready(function(twttr) {
		clearInterval(spu_counter);
		twttr.events.bind('tweet', twitterCB);
		twttr.events.bind('follow', twitterCB);
	});
}
});
function twitterCB(intent_event) {
	thanks_msg(window.options);
}

function googleCB(a) {
	clearInterval(spu_counter);
	if( "on" == a.state )
	{
	 //	setTimeout(function(){thanks_msg(window.options)},2500);
	}

}
function closeGoogle(a){
	if( "confirm" == a.type )
	{
	setTimeout(function(){thanks_msg(window.options)},2500);
	}
}

function spuFlush( days ) {
	days = typeof days !== 'undefined' ? days : 99;
	createCookie('spushow', 'true', days);
	
	jQuery("#spu-bg").fadeOut("slow");
	jQuery("#spu-main").fadeOut("slow");
}

function createCookie(name, value, days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
		var expires = "; expires=" + date.toGMTString();
	} else var expires = "";
	document.cookie = name + "=" + value + expires + "; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') c = c.substring(1, c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
	}
	return null;
}
function spu_timer(defaults)
{
  spu_count=spu_count-1;
  if (spu_count <= 0)
  {
     clearInterval(spu_counter);
     spuFlush(defaults.days_no_click);
     return;
  }

 jQuery("#spu-timer").html(defaults.esperar+" "+spu_count + " " + defaults.segundos);
}