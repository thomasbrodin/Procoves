$(window).load(function() {
	$('.slider').flexslider({
		slideshow: false, 
		animation: "slide", 
		animationLoop: false, 
		controlNav: false,
		controlsContainer: "#controlswrap .container",
		keyboard: true,
	});
	$('.thumbslider').flexslider({
		slideshow: false,
		animation: "slide",
		animationLoop: true,
		itemWidth: 200,
		itemMargin: 28,
		controlNav: false,
		minItems: 2,
		maxItems: 4
  	});
});

jQuery(document).ready(function($) {

		$('.navlist li a').on('click', function() {
			var scrollAnchor = $(this).attr('data-scroll'),
				scrollPoint = $('section[data-anchor="' + scrollAnchor + '"]').offset().top - 267;
			$('body,html').animate({
				scrollTop: scrollPoint
			}, 500);   
			return false;
		});
		$('#back-to-top').on('click', function() {
			$('body,html').animate({scrollTop: 0}, 200);   
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
		$('.thumbslider ul.slides li').bind('mouseenter',function() {
			var height = $(this).height();
			var width = $(this).width();
			$(this).children('.caption_overlay').css({'height':height, 'width':width});
			$(this).children('.caption_overlay').animate({'opacity':'1'},'fast');
        }).bind('mouseleave',function() {
			$(this).children('.caption_overlay').animate({'opacity':'0'},'slow');
        });
        $('.gamme-thumb').bind('mouseenter',function() {
			var height = $(this).height();
			var width = $(this).width();
			$(this).children('.caption_overlay').css({'height':height, 'width':width});
			$(this).children('.caption_overlay').animate({'opacity':'1'},'fast');
        }).bind('mouseleave',function() {
			$(this).children('.caption_overlay').animate({'opacity':'0'},'slow');
        });
        $('.related-thumb').bind('mouseenter',function() {
			var height = $(this).height();
			var width = $(this).width();
			$(this).children('.caption_overlay').css({'height':height, 'width':width});
			$(this).children('.caption_overlay').animate({'opacity':'1'},'fast');
        }).bind('mouseleave',function() {
			$(this).children('.caption_overlay').animate({'opacity':'0'},'slow');
        });
		$('#navside').affix({
			offset: {
				top: 0,
				bottom: 85
				}
		});
		$("[data-toggle=tooltip").tooltip();
});