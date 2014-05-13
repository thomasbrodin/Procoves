(function($) {
	//Facets events
	$('section#content .row.search').hide();
	$('.row.home .facetwp-template').hide();
	$('.loading').show();
	$(document).on('facetwp-refresh', function() {
		$('.facet-loading').show();
		$('.ajax-hide').hide();
		$('html, body').animate({ scrollTop: 0 }, 200);
		$('.facetwp-template .produit-thumb').css("opacity","0.5");
		if (FWP.loaded) { // wait until the first user interaction
			$('.collection .gammes').hide();
			$('.facetwp-template').show();
		}
	});
	$(document).on('facetwp-loaded', function() {
		$('.loading').hide();
		$('section#content .row.search').show();
		$('.facetwp-template .produit-thumb').css("opacity","1");
		$('.facet-loading').hide();
		$('.ajax-hide').show();
		$( '[data-value=""]' ).addClass( "button" );
		$('.facetwp-facet').each(function() {
			$(this).closest('.box-wrap').show();
				if ('' == $(this).html()) {
					$(this).closest('.box-wrap').hide();
				}
		});
		//Input
		$('input[type="text"].sidebar').each(function() {
			if ($(this).val() !== '') {
				$(this).addClass('used');
			}
		});
		//Sticky sidebar
		var sticky = $('.sticky');
		var stickyHeight = $('.sticky').height();
		var stickyTop = $('.sticky').offset().top;
		$(window).scroll(function(){
			var WindowTop = $(window).scrollTop();
			if (stickyTop < WindowTop){
				sticky.css('top', WindowTop);
			} else {
				sticky.css('top',0);
			}
		});
	});
	//Scroll top
	$('.navlist li a').on('click', function() {
			var scrollAnchor = $(this).attr('data-scroll'),
				scrollPoint = $('section[data-anchor="' + scrollAnchor + '"]').offset().top - 235;
			$('body,html').animate({
				scrollTop: scrollPoint
			}, 500);
			return false;
	});
	$('#back-to-top').on('click', function() {
		$('body,html').animate({scrollTop: 0}, 500);
	});
	//Navigation slider
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
	//Overlays
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
    // Fixed nav pages
	$('#navside .nav-fix').affix({
		offset: {
			top: 0,
			bottom: 85
		}
	});
	// Tooltip
	$("[data-toggle=tooltip]").tooltip();
	// Slider
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
})(jQuery);
