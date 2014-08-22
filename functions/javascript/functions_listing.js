/**
* Core Javascript functions for ILance listing pages
*
* @package      iLance\Javascript\Listing
* @version	4.0.0.8059
* @author       ILance
*/
(function($){
	$.fn.illisting = function() {
		if (jQuery('.carousel_listing_gallery_thumb').length)
		{
			jQuery(".carousel_listing_gallery_thumb").jCarouselLite({
				btnNext:"#c5r",
				btnPrev:"#c5l",
				easing:"easeOutQuad",
				visible:4,
				scroll:1,
				speed:100
			});
		}
		if (jQuery(".carousel_recentviewed_1col ul li").length < 5)
		{
			jQuery("#c5r").addClass('disabled');
		}
	};
})(jQuery);
$(document).ready(function () {
	(function(){
		jQuery().illisting();
	}());
});