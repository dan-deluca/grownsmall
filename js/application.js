(function($) {
	"use strict";
// Accordion Toggle Items
   var iconOpen = 'fa fa-minus',
        iconClose = 'fa fa-plus';

    $(document).on('show.bs.collapse hide.bs.collapse', '.accordion', function (e) {
        var $target = $(e.target)
          $target.siblings('.accordion-heading')
          .find('em').toggleClass(iconOpen + ' ' + iconClose);
          if(e.type == 'show')
              $target.prev('.accordion-heading').find('.accordion-toggle').addClass('active');
          if(e.type == 'hide')
              $(this).find('.accordion-toggle').not($target).removeClass('active');
    });
// Count Script
	function count($this){
		var current = parseInt($this.html(), 10);
		current = current + 1; /* Where 50 is increment */
	
		$this.html(++current);
		if(current > $this.data('count')){
			$this.html($this.data('count'));
		} else {    
			setTimeout(function(){count($this)}, 50);
		}
	}        
	
	$(".stat-count").each(function() {
	  $(this).data('count', parseInt($(this).html(), 10));
	  $(this).html('0');
	  count($(this));
	});
	
// Tooltip
	$('.social, .client').tooltip({
		selector: "a[data-toggle=tooltip]"
	})
	
	$('.social, .client').tooltip()

// DM Menu
	jQuery('#nav').affix({
		offset: { top: $('#nav').offset().top }
	});

	
// Menu
	$(".panel a").click(function(e){
		e.preventDefault();
			var style = $(this).attr("class");
			$(".jetmenu").removeAttr("class").addClass("jetmenu").addClass(style);
		});
	$().jetmenu();

// Back to Top
 jQuery(window).scroll(function(){
	if (jQuery(this).scrollTop() > 1) {
			jQuery('.dmtop').css({bottom:"25px"});
		} else {
			jQuery('.dmtop').css({bottom:"-100px"});
		}
	});
	jQuery('.dmtop').click(function(){
		jQuery('html, body').animate({scrollTop: '0px'}, 800);
		return false;
});


//Add Hover effect to menus
jQuery('.btn-group').hover(function() {
  jQuery(this).find('.dropdown-menu').stop(true, true).delay(100).fadeIn();
}, function() {
  jQuery(this).find('.dropdown-menu').stop(true, true).delay(100).fadeOut();
});

// Preloader
	$(window).load(function() {
		$('#status').delay(100).fadeOut('slow');
		$('#preloader').delay(100).fadeOut('slow');
		$('body').delay(100).css({'overflow':'visible'});
	})

// Testimonials
			if($('#testimonials').length){
		// Randomise
		$('.testimonial-nav').each(function(){
		    var container = $(this),
		    	children = container.children('li');
		    children.sort(function(a,b){
		          var temp = parseInt( Math.random()*8 );
		          var isOddOrEven = temp%2;
		          var isPosOrNeg = temp>5 ? 1 : -1;
		          return( isOddOrEven*isPosOrNeg );
		    })
		    .appendTo(container);            
		});

		$('#testimonials .testimonial:eq(8),#testimonials .testimonial-nav a:eq(8)').addClass('active');
		$('#testimonials .testimonial-nav a').hover(function(){
			$('#testimonials .testimonial-nav a,#testimonials .testimonial').removeClass('active');
			$(this).addClass('active');
			$($(this).attr('href')).addClass('active');
		});
		$('#testimonials .testimonial-nav a').click(function(){ return false; });
	}
})(jQuery);