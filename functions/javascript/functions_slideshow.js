/**
* Core javascript image slideshow functions within ILance.
*
* @package      iLance\Javascript\SlideShow
* @version	4.0.0.8059
* @author       ILance
*/
function next()
{
	if (fetch_js_object('slidepulldown')[current + 1])
	{
		fetch_js_object('slide_show_image').src = fetch_js_object('slidepulldown')[current + 1].value;
		fetch_js_object('slidepulldown').selectedIndex = ++current;
	}
	else first();
}

function previous()
{
	if (current - 1 >= 0)
	{
		fetch_js_object('slide_show_image').src = fetch_js_object('slidepulldown')[current - 1].value;
		fetch_js_object('slidepulldown').selectedIndex = --current;
	}
	else last();
}

function first()
{
	current = 0;
	fetch_js_object('slide_show_image').src = fetch_js_object('slidepulldown')[0].value;
	fetch_js_object('slidepulldown').selectedIndex = 0;
}

function last()
{
	current = fetch_js_object('slidepulldown').length - 1;
	fetch_js_object('slide_show_image').src = fetch_js_object('slidepulldown')[current].value;
	fetch_js_object('slidepulldown').selectedIndex = current;
}

function ap(text)
{
	fetch_js_object('slidebutton').value = (text == 'Stop') ? 'Start' : 'Stop';
	rotate();
}

function change()
{
	current = fetch_js_object('slidepulldown').selectedIndex;
	fetch_js_object('slide_show_image').src = fetch_js_object('slidepulldown')[current].value;
}

function rotate()
{
	if (fetch_js_object('slidebutton').value == 'Stop')
	{
		current = (current == fetch_js_object('slidepulldown').length - 1) ? 0 : current + 1;
		fetch_js_object('slide_show_image').src = fetch_js_object('slidepulldown')[current].value;
		fetch_js_object('slidepulldown').selectedIndex = current;
		
		window.setTimeout("rotate()", rotate_delay);
	}
}