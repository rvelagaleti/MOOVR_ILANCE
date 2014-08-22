// Copyright 2010 htmldrive.net Inc.
/**
* @projectHomepage http://www.htmldrive.net/welcome/amazon-scroller
* @projectDescription Amazon style image and title scroller
* @author htmldrive.net
* More script and css style : htmldrive.net
* @version 1.0
* @license http://www.apache.org/licenses/LICENSE-2.0
*/ 
(function(a){
    a.fn.amazon_scroller=function(p){
        var p = p||{};
        var g = p&&p.scroller_time_interval?p.scroller_time_interval:"3000";
        var h = p&&p.scroller_title_show?p.scroller_title_show:"enable";
        var i = p&&p.scroller_window_background_color?p.scroller_window_background_color:"white";
        var j = p&&p.scroller_window_padding?p.scroller_window_padding:"5";
        var k = p&&p.scroller_border_size?p.scroller_border_size:"1";
        var l = p&&p.scroller_border_color?p.scroller_border_color:"black";
        var m = p&&p.scroller_images_width?p.scroller_images_width:"70";
        var n = p&&p.scroller_images_height?p.scroller_images_height:"50";
        var o = p&&p.scroller_title_size?p.scroller_title_size:"12";
        var q = p&&p.scroller_title_color?p.scroller_title_color:"blue";
        var r = p&&p.scroller_show_count?p.scroller_show_count:"3";
        var d = p&&p.directory?p.directory:"images";
	var ao = p&&p.scroller_scroll_atonce?p.scroller_scroll_atonce:"1";
	j += "px";
        k += "px";
        m += "px";
        n += "px";
        o += "px";
        var dom = a(this);
        var s;
        var t = 0;
        var u;
        var v;
        var w = dom.find("ul:first").children("li").length;
        var x = Math.ceil(w / r);
	var lis = parseInt(dom.find("ul:first").children("li").length); // number of <li>'s
        if (dom.find("ul").length == 0 || dom.find("li").length == 0)
	{
            dom.append("Require content");
            return null
        }
        dom.find("ul:first").children("li").children("a").children("img").css("width", m).css("height", n);
	h = 'disable';
	dom.find(".amazon_scroller_nav").children("li:first").css("cursor", "default");
	dom.find(".amazon_scroller_nav").children("li:first").css({opacity: 0.3});
	if (lis == 1)
	{
	    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "default");
	    dom.find(".amazon_scroller_nav").children("li:last").css({opacity: 0.3});
	}
	if (parseInt(r) > lis)
	{
	    r = lis;
	}
	if (lis == parseInt(r))
	{
	    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "default");
	    dom.find(".amazon_scroller_nav").children("li:last").css({opacity: 0.3});
	}
        s_s_ul(dom, j, k, l, i);
        s_s_nav(dom.find(".amazon_scroller_nav"), d);
	if (ao > parseInt(r))
	{
	    ao = parseInt(r);
	}
	if (ao > lis)
	{
	    ao = lis;
	}
	m = parseInt(m);
        dom.find("ul:first").children("li").css("width", m + "px");
        n = parseInt(n);
        begin();
        s = setTimeout(play, g);
        dom.find(".amazon_scroller_nav").children("li").hover(function() 
	{
	    if ($(this).parent().children().index($(this)) == 0)
	    {	
		if (parseInt(t*ao) == 0)
		{
		}
		else
		{
		    $(this).css("cursor", "pointer");
		    $(this).css("cursor", "hand");
		    $(this).css("background-position","left -50px");
		}
	    }
	    else if ($(this).parent().children().index($(this)) == 1)
	    {
		if ((parseInt(r) + parseInt(t*ao)) == lis)
		{
		}
		else
		{
		    $(this).css("cursor", "pointer");
		    $(this).css("cursor", "hand");
		    $(this).css("background-position","right -50px");
		}
	    }
        },function()
	{
	    if ($(this).parent().children().index($(this)) == 0)
	    {
		$(this).css("background-position","left top");
	    }
	    else if ($(this).parent().children().index($(this)) == 1)
	    {
		$(this).css("background-position","right top");
	    }
	});
        dom.find(".amazon_scroller_nav").children("li").click(function()
	{
            if ($(this).parent().children().index($(this)) == 0)
	    {
                previous();
            }
	    else if ($(this).parent().children().index($(this)) == 1)
	    {
                next();
            }
        });
        dom.hover(function()
	{
                clearTimeout(s);
        },function()
	{
                s = setTimeout(play, g);
        });
        function begin()
	{
            var a = dom.find("ul:first").children("li").outerWidth(true) * w;
            dom.find("ul:first").children("li").hide();
            dom.find("ul:first").children("li").slice(0, r).show();
            u = dom.find("ul:first").outerWidth();
            v = dom.find("ul:first").outerHeight();
            dom.find("ul:first").width(a);
            dom.width(u + 60);
            dom.height(v);
            dom.children(".amazon_scroller_mask").width(u);
            dom.children(".amazon_scroller_mask").height(v);
            dom.find("ul:first").children("li").show();
            dom.css("position", "relative");
            dom.find("ul:first").css("position", "absolute");
            dom.children(".amazon_scroller_mask").width(u);
            dom.children(".amazon_scroller_mask").height(v);
            dom.find(".amazon_scroller_nav").css('top', (v - 110) / 2 + parseInt(j) + "px");
            dom.find(".amazon_scroller_nav").width(u + 60);
        }
        function previous()
	{
	    clearTimeout(s);
	    if (parseInt(t*ao) > 0)
	    {
		t--;
		dom.children(".amazon_scroller_mask").find("ul").animate({left: '+=' + ((m*ao) + (10*ao))}, 100);
		if (parseInt(t*ao) == 0)
		{
		    dom.find(".amazon_scroller_nav").children("li:first").css("cursor", "default");
		    dom.find(".amazon_scroller_nav").children("li:first").css({opacity: 0.3});
		    dom.find(".amazon_scroller_nav").children("li:first").css("background-position", "left top"); 
		}
		else (parseInt(t*ao) > 0)
		{
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "pointer");
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "hand");
		    dom.find(".amazon_scroller_nav").children("li:last").css({opacity: 1});
		    dom.find(".amazon_scroller_nav").children("li:last").css("background-position", "right top");
		}
	    }
	    else if (parseInt(t*ao) == 0)
	    {
		if (lis == 1)
		{
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "default");
		    dom.find(".amazon_scroller_nav").children("li:last").css({opacity: 0.3});
		}
		else
		{
		    dom.find(".amazon_scroller_nav").children("li:first").css("cursor", "default");
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "pointer");
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "hand");
		    dom.find(".amazon_scroller_nav").children("li:first").css({opacity: 0.3});
		    dom.find(".amazon_scroller_nav").children("li:last").css({opacity: 1});
		}
	    }
        }
        function next()
	{
            play();
        }
        function play()
	{
	    t++;
	    var a = dom.find("ul:first").children("li").outerWidth(true) * w;
            if (parseInt(t*ao) >= w + 1)
	    {
		t = 0;
		dom.children(".amazon_scroller_mask").find("ul:first").css("left", "0px");
		dom.children(".amazon_scroller_mask").find("ul:last").css("left", a);
		s = setTimeout(play, 0);
            }
	    else
	    {
		if (lis > (parseInt(r) + parseInt(t*ao)))
		{
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "pointer");
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "hand");
		    dom.find(".amazon_scroller_nav").children("li:last").css({opacity: 1});
		    dom.find(".amazon_scroller_nav").children("li:last").css("background-position","right -50px");
		    dom.find(".amazon_scroller_nav").children("li:first").css("cursor", "pointer");
		    dom.find(".amazon_scroller_nav").children("li:first").css("cursor", "hand");
		    dom.find(".amazon_scroller_nav").children("li:first").css({opacity: 1});
		    dom.find(".amazon_scroller_nav").children("li:first").css("background-position","left top");
		    //dom.children(".amazon_scroller_mask").find("ul").animate({left: '-=' + (m + 10)}, 100);
		    dom.children(".amazon_scroller_mask").find("ul").animate({left: '-=' + ((m*ao) + (10*ao))}, 100);
		    s = setTimeout(play, g);
		}
		else if (lis == (parseInt(r) + parseInt(t*ao)))
		{
		    dom.find(".amazon_scroller_nav").children("li:last").css("cursor", "default");
		    dom.find(".amazon_scroller_nav").children("li:last").css({opacity: 0.3});
		    dom.find(".amazon_scroller_nav").children("li:last").css("background-position","right top");
		    dom.find(".amazon_scroller_nav").children("li:first").css("cursor", "pointer");
		    dom.find(".amazon_scroller_nav").children("li:first").css("cursor", "hand");
		    dom.find(".amazon_scroller_nav").children("li:first").css({opacity: 1});
		    dom.find(".amazon_scroller_nav").children("li:first").css("background-position","left top");
		    //dom.children(".amazon_scroller_mask").find("ul").animate({left: '-=' + (m + 10)}, 100);
		    dom.children(".amazon_scroller_mask").find("ul").animate({left: '-=' + ((m*ao) + (10*ao))}, 100);
		    s = setTimeout(play, g);
		}
		else if (lis < (parseInt(r) + parseInt(t*ao)))
		{
		    t--;
		}
	    }
        }
        function s_s_ul(a,b,c,d,e)
	{
            b = parseInt(b);
            c = parseInt(c);
            var f = "border: " + d + " solid "+" " + c + "px;padding:" + b + "px;background-color:" + e;
            a.attr("style", f)
        }
        function s_s_nav(a, d)
	{
            var b = a.children("li:first");
            var c = a.children("li:last");
            a.children("li").css("width", "25px");
            a.children("li").css("height", "50px");
	    a.children("li").css('background-image', 'url(\'' + d + 'arrow_sprite.gif\')');
            c.css('background-position', 'right top');
            a.children("li").css("background-repeat", "no-repeat");
            c.css("right", "0px");
            b.css("left", "0px");
        }
    }
})(jQuery);
if (typeof jQuery != 'undefined')
{
    jQuery(function() {
	    jQuery('#amazon_scroller_featured_items').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_featured_items6').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '6',
	    scroller_scroll_atonce: '6',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_featured_jobs').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_featured_jobs6').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '6',
	    scroller_scroll_atonce: '6',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_endingsoon_jobs').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_endingsoon_items').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_latest_jobs').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_latest_items').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_latest_items6').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '6',
	    scroller_scroll_atonce: '6',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_recent_jobs').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '1',
	    scroller_scroll_atonce: '1',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_recent_items').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '1',
	    scroller_scroll_atonce: '1',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_watchlist_items').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_search_results').amazon_scroller({
	    scroller_title_show: 'enable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_recently_viewed').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    jQuery(function() {
	    jQuery('#amazon_scroller_other_items').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '8',
	    scroller_scroll_atonce: '6',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
    /*store items*/
    jQuery(function() {
	    jQuery('#amazon_scroller_latest_store_items').amazon_scroller({
	    scroller_title_show: 'disable',
	    scroller_time_interval: '500000',
	    scroller_window_background_color: 'none',
	    scroller_window_padding: '0',
	    scroller_border_size: '0',
	    scroller_border_color: '#CDCDCD',
	    scroller_images_width: THUMBWIDTH,
	    scroller_images_height: THUMBHEIGHT,
	    scroller_title_size: '0',
	    scroller_title_color: 'black',
	    scroller_show_count: '5',
	    scroller_scroll_atonce: '5',
	    scroller_images_width_separator: '10',
	    directory: IMAGEBASE
	    });
    });
}