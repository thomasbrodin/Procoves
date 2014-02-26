
$(window).load(function() {
	$('.slider').flexslider({
		slideshow: false, 
		animation: "slide", 
		animationLoop: false, 
		controlNav: false,
		controlsContainer: "#controlswrap .container",
		keyboard: true,
	});
});

jQuery(document).ready(function($) {

		$('.navlist li a').on('click', function() {
		var scrollAnchor = $(this).attr('data-scroll'),
			scrollPoint = $('section[data-anchor="' + scrollAnchor + '"]').offset().top - 300;
		$('body,html').animate({
			scrollTop: scrollPoint
		}, 500);   
		return false;
		});
		var nav_index;
		$("#access li.nav-main-item").hover(function(){
			$("#access .indicator").addClass("on");
			nav_index == $('#access-js-slider').attr('class');
			var slider_class = $(this).attr("data-index");
			$("#access-js-slider").removeClass().addClass(slider_class);
		}, function(){
			$('#access-js-slider').removeClass().addClass(nav_index);
			$("#access .indicator").removeClass("on");});
			$("#access li.nav-main-item").click(function(){
			nav_index = $("#access-js-slider").attr("class");
		});
		$('#navside').affix({
			offset: {
				top: 0,
			}
		});
		$("[data-toggle=tooltip").tooltip();
});