/**
* Core Javascript functions for ILance 4.x
*
* @package      iLance\Javascript\Core_4
* @version	4.0.0.8059
* @author       ILance
*/
jQuery(document).ready(function()
{
	var moreinfotracker = new Array();
	var moreinfonumber;
	if (jQuery('.select3').length)
	{
		jQuery('.select3').css('visibility', 'visible');
		jQuery('select.select3').each(function() {
			var title = jQuery(this).attr('title');
			var mode = jQuery(this).val();
			if (jQuery('option:selected', this).val() != '')
			{
				title = jQuery('option:selected', this).text();
				mode = jQuery('option:selected', this).val();
			}
			jQuery(this).css({'z-index':10, 'opacity':0, '-khtml-appearance':'none'}).after('<span class="select3">' + title + '</span>').change(function()
			{
				val = jQuery('option:selected', this).text();
				opt = jQuery('option:selected', this).val();
				jQuery(this).next().text(val);
			})
		});
	}
	if (jQuery('.setting').length)
	{
		jQuery(".setting").click(function () {
			jQuery('.setting-panel').slideToggle();
			jQuery('.setting').toggleClass("active");
			return false;
		});
	}
	if (jQuery('.menu-link').length)
	{
		jQuery(".menu-link").click(function () {
			jQuery('.search-menu-list').toggle();
			jQuery('.search-menu').toggleClass('active');
			return false;
		});
	}
	if (jQuery('.search-menu-list a').length)
	{
		jQuery(".search-menu-list a").click(function () {
			jQuery('.search-menu-list').hide();
			jQuery('.search-menu').removeClass('active');			
			return false;
		});
	}
	if (jQuery('#search-menu ul li a').length)
	{
		jQuery('#search-menu ul li a').click(function(){
			val = jQuery(this).text();
			catid = jQuery(this).attr("data-catid");
			jQuery("#cidfield").val(catid);
			jQuery('#search-menu-selected').text(val);
			jQuery('#search_keywords_id').focus();
		});
	}
	if (jQuery('.top-dropdown2 .txtb').length)
	{
		jQuery('.top-dropdown2 .txtb').hide();
		jQuery('.top-dropdown2 h4:first').addClass('active').next().show();
		jQuery('.top-dropdown2 h4').click(function() {
			if (jQuery(this).next().is(':hidden')) {
				jQuery('.top-dropdown2 h4').removeClass('active').next().slideUp(); 
				jQuery(this).toggleClass('active').next().slideDown();
			}
			return false; 
		});
	}
	jQuery('.favorite').one('mouseenter', function()
	{
		if (parseInt(UID) > 0)
		{
			setTimeout("print_favourite_items(5)", 500);
		}
		return false;
	});
	jQuery(".arrow-link").click(function ()
	{
		jQuery('.product-list').slideToggle();
		jQuery('.arrow-link').toggleClass("active");
		return false;
	});
	jQuery('.arrow-link').one('click', function()
	{
		if (jQuery('.arrow-link').hasClass('active'))
		{
			print_recently_viewed_items('load', 3, PAGEURL);
		}
		return false;
	});
	jQuery('.product-list .close').click(function()
	{
		jQuery('.product-list').slideUp();
		return false;
	});
	jQuery('.info-link1').click(function ()
	{
		setTimeout('print_search_result_moreinfo(' + this.id + ')', 500);
		if (moreinfotracker.length > 0)
		{
			moreinfonumber = moreinfotracker.pop();
			while (moreinfonumber != undefined)
			{
				jQuery('.info-box1-' + moreinfonumber).hide();
				moreinfonumber = moreinfotracker.pop();
			}
			jQuery('.info-box1-' + this.id).slideToggle();
			moreinfotracker.length = 0;
		}
		else
		{
			jQuery('.info-box1-' + this.id).slideToggle();
		}
		moreinfotracker.push(this.id);
                return false;
        });  	
	jQuery('.info-box1-close').click(function ()
	{
		jQuery('.info-box1-' + this.id).hide();
		moreinfotracker.length = 0;
		return false;
	});
	jQuery('.info-link4').click(function ()
	{
		if (moreinfotracker.length > 0)
		{
			moreinfonumber = moreinfotracker.pop();
			while (moreinfonumber != undefined)
			{
				jQuery('.info-box4-' + moreinfonumber).hide();
				moreinfonumber = moreinfotracker.pop();
			}
			jQuery('.info-box4-' + this.id).slideToggle();
			moreinfotracker.length = 0;
		}
		else
		{
			jQuery('.info-box4-' + this.id).slideToggle();
		}
		moreinfotracker.push(this.id);
                return false;
        });  	
	jQuery('.info-box4-close').click(function ()
	{
		jQuery('.info-box4-' + this.id).hide();
		moreinfotracker.length = 0;
		return false;
	});
});