/**
 * Tabify
 */
;(function ($) {

	'use strict';

	$.fn.tabify = function () {
		return this.each(function () {
			var tabs = $(this);
			if ( ! tabs.data( 'tabify' ) ) {
				tabs.data( 'tabify', true );
				$('ul.tab-nav li:first', tabs).addClass('current');
				$('div:first', tabs).show();
				var tabLinks = $('ul.tab-nav li', tabs);
				$(tabLinks).click(function () {
					$(this).addClass('current').attr( 'aria-expanded', 'true' ).siblings().removeClass('current').attr( 'aria-expanded', 'false' );
					$('.tab-content', tabs).hide().attr( 'aria-hidden', 'true' );
					var activeTab = $(this).find('a').attr( 'href' );
					$(activeTab).show().attr( 'aria-hidden', 'false' ).trigger( 'resize' );
					$( 'body' ).trigger( 'tf_tabs_switch', [ activeTab, tabs ] );
					if ( $(activeTab).find('.shortcode.map').length > 0 ) {
						$(activeTab).find('.shortcode.map').each(function(){
							var mapInit = $(this).find('.map-container').data('map'),
								center = mapInit.getCenter();
							google.maps.event.trigger(mapInit, 'resize');
							mapInit.setCenter(center);
						});
					}
					return false;
				});
				$('.tab-content', tabs).find('a[href^="#tab-"]').on('click', function(event){
					event.preventDefault();
					var dest = $(this).prop('hash').replace('#tab-', ''),
						contentID = $('.tab-content', tabs).eq( dest - 1 ).prop('id');
					if ( $('a[href^="#'+ contentID +'"]').length > 0 ) {
						$('a[href^="#'+ contentID +'"]').trigger('click');
					}
				});
			}
		});
	};

	// $('img.photo',this).themifyBuilderImagesLoaded(myFunction)
	// execute a callback when all images have loaded.
	// needed because .load() doesn't work on cached images
	$.fn.themifyBuilderImagesLoaded = function(callback){
	  var elems = this.filter('img'),
		  len   = elems.length,
		  blank = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
		  
	  elems.bind('load.imgloaded',function(){
		  if (--len <= 0 && this.src !== blank){ 
			elems.unbind('load.imgloaded');
			callback.call(elems,this); 
		  }
	  }).each(function(){
		 // cached images don't fire load sometimes, so we reset src.
		 if (this.complete || this.complete === undefined){
			var src = this.src;
			// webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
			// data uri bypasses webkit log warning (thx doug jones)
			this.src = blank;
			this.src = src;
		 }  
	  }); 
	 
	  return this;
	};
})(jQuery);

/*
 * Parallax Scrolling Builder
 */
(function( $ ){

	'use strict';

	var $window = $(window);
	var windowHeight = $window.height();

	$window.resize(function () {
		windowHeight = $window.height();
	});

	$.fn.builderParallax = function(xpos, speedFactor, outerHeight) {
		var $this = $(this);
		var getHeight;
		var firstTop;
		var paddingTop = 0, resizeId;
		
		//get the starting position of each element to have parallax applied to it		
		$this.each(function(){
			firstTop = $this.offset().top;
		});
		$window.resize(function(){
			clearTimeout(resizeId);
			resizeId = setTimeout(function(){
				$this.each(function(){
					firstTop = $this.offset().top;
				});
			}, 500);
		});

		if (outerHeight) {
			getHeight = function(jqo) {
				return jqo.outerHeight(true);
			};
		} else {
			getHeight = function(jqo) {
				return jqo.height();
			};
		}
			
		// setup defaults if arguments aren't specified
		if (arguments.length < 1 || xpos === null) xpos = "50%";
		if (arguments.length < 2 || speedFactor === null) speedFactor = 0.1;
		if (arguments.length < 3 || outerHeight === null) outerHeight = true;
		
		// function to be called whenever the window is scrolled or resized
		function update(){
			var pos = $window.scrollTop();				

			$this.each(function(){
				var $element = $(this);
				var top = $element.offset().top;
				var height = getHeight($element);

				// Check if totally above or totally below viewport
				if (top + height < pos || top > pos + windowHeight) {
					return;
				}

				$this.css('backgroundPosition', xpos + " " + Math.round((firstTop - pos) * speedFactor) + "px");
			});
		}		

		$window.bind('scroll', update).resize(update);
		update();
	};
})(jQuery);