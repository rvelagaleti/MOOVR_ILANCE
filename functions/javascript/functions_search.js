/**
* Core javascript search functions within ILance.
*
* @package      iLance\Javascript\Search
* @version	4.0.0.8059
* @author       ILance
*/
function enable_input_group(inputgroup, checkbool, force)
{
	if (inputgroup == 'hourly')
	{
		if (checkbool)
		{
			fetch_js_object('hourlyfromprice').disabled = false;
			fetch_js_object('hourlytoprice').disabled = false;
			if (force)
			{
				fetch_js_object('cb_hourly').checked = true;
			}
		}
		else
		{
			fetch_js_object('hourlyfromprice').disabled = true;
			fetch_js_object('hourlytoprice').disabled = true;
		}
	}
	else if (inputgroup == 'distance_experts')
	{
		if (checkbool)
		{
			fetch_js_object('expertradius').disabled = false;
			fetch_js_object('expertradiuszip').disabled = false;
			
			if (force)
			{
				fetch_js_object('cb_expertdistance').checked = true;
			}
		}
		else
		{
			fetch_js_object('expertradius').disabled = true;
			fetch_js_object('expertradiuszip').disabled = true;
		}
	}
	else if (inputgroup == 'distance_service')
	{
		if (checkbool)
		{
			fetch_js_object('serviceradius').disabled = false;
			fetch_js_object('serviceradiuszip').disabled = false;
			
			if (force)
			{
				fetch_js_object('cb_servicedistance').checked = true;
			}
		}
		else
		{
			fetch_js_object('serviceradius').disabled = true;
			fetch_js_object('serviceradiuszip').disabled = true;
		}
	}
	else if (inputgroup == 'distance_product')
	{
		if (checkbool)
		{
			fetch_js_object('productradius').disabled = false;
			fetch_js_object('productradiuszip').disabled = false;
			
			if (force)
			{
				fetch_js_object('cb_productdistance').checked = true;
			}
		}
		else
		{
			fetch_js_object('productradius').disabled = true;
			fetch_js_object('productradiuszip').disabled = true;
		}
	}
	else if (inputgroup == 'location_experts')
	{
		if (checkbool)
		{
			fetch_js_object('expertcountry').disabled = false;
			fetch_js_object('incity3_3').disabled = false;
			fetch_js_object('state3_3').disabled = false;
			fetch_js_object('zipcode3_3').disabled = false;
			fetch_js_object('cb_expertregion').checked = false;
			fetch_js_object('cb_expertregion').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_expertdistance').disabled = false;
				fetch_js_object('cb_expertdistance').checked = false;
				toggle_show('toggleradiusexperts');
			}
			
			if (force)
			{
				fetch_js_object('cb_expertlocation').checked = true;
			}
		}
		else
		{
			fetch_js_object('expertcountry').disabled = true;
			fetch_js_object('incity3_3').disabled = true;
			fetch_js_object('state3_3').disabled = true;
			fetch_js_object('zipcode3_3').disabled = true;
			fetch_js_object('cb_expertregion').disabled = false;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_expertdistance').disabled = true;
				fetch_js_object('expertradius').disabled = true;
				fetch_js_object('expertradiuszip').disabled = true;
				toggle_hide('toggleradiusexperts');
			}
		}
	}
	else if (inputgroup == 'location_service')
	{
		if (checkbool)
		{
			fetch_js_object('servicecountry').disabled = false;
			fetch_js_object('incity1_3').disabled = false;
			fetch_js_object('state1_3').disabled = false;
			fetch_js_object('zipcode1_3').disabled = false;
			fetch_js_object('cb_serviceregion').checked = false;
			fetch_js_object('cb_serviceregion').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_servicedistance').disabled = false;
				fetch_js_object('cb_servicedistance').checked = false;
				toggle_show('toggleradiusservice');
			}
			
			if (force)
			{
				fetch_js_object('cb_servicelocation').checked = true;
			}
		}
		else
		{
			fetch_js_object('servicecountry').disabled = true;
			fetch_js_object('incity1_3').disabled = true;
			fetch_js_object('state1_3').disabled = true;
			fetch_js_object('zipcode1_3').disabled = true;
			fetch_js_object('cb_serviceregion').disabled = false;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_servicedistance').disabled = true;
				fetch_js_object('serviceradius').disabled = true;
				fetch_js_object('serviceradiuszip').disabled = true;
				toggle_hide('toggleradiusservice');
			}
		}
	}	
	else if (inputgroup == 'location_product')
	{
		if (checkbool)
		{
			fetch_js_object('productcountry').disabled = false;
			fetch_js_object('incity2_3').disabled = false;
			fetch_js_object('state2_3').disabled = false;
			fetch_js_object('zipcode2_3').disabled = false;
			fetch_js_object('cb_productregion').checked = false;
			fetch_js_object('cb_productregion').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_productdistance').disabled = false;
				fetch_js_object('cb_productdistance').checked = false;
				toggle_show('toggleradiusproduct');
			}
			
			if (force)
			{
				fetch_js_object('cb_productlocation').checked = true;
			}
		}
		else
		{
			fetch_js_object('productcountry').disabled = true;
			fetch_js_object('incity2_3').disabled = true;
			fetch_js_object('state2_3').disabled = true;
			fetch_js_object('zipcode2_3').disabled = true;
			fetch_js_object('cb_productregion').disabled = false;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_productdistance').disabled = true;
				fetch_js_object('productradius').disabled = true;
				fetch_js_object('productradiuszip').disabled = true;
				toggle_hide('toggleradiusproduct');
			}
		}
	}
	else if (inputgroup == 'region_service')
	{
		if (checkbool)
		{
			fetch_js_object('rb_serviceregionfrom').disabled = false;
			fetch_js_object('rb_serviceregionto').disabled = false;
			fetch_js_object('rb_serviceregionin').disabled = false;
			fetch_js_object('cb_servicelocation').checked = false;
			fetch_js_object('cb_servicelocation').disabled = true;
			
			if (fetch_js_object('rb_serviceregionfrom').checked == true)
			{
				fetch_js_object('serviceregionin').disabled = false;
				fetch_js_object('servicecountryto').disabled = true;
				fetch_js_object('servicecountryin').disabled = true;
			}
			else if (fetch_js_object('rb_serviceregionto').checked == true)
			{
				fetch_js_object('serviceregionin').disabled = true;
				fetch_js_object('servicecountryto').disabled = false;
				fetch_js_object('servicecountryin').disabled = true;
			}
			else if (fetch_js_object('rb_serviceregionin').checked == true)
			{
				fetch_js_object('serviceregionin').disabled = true;
				fetch_js_object('servicecountryto').disabled = true;
				fetch_js_object('servicecountryin').disabled = false;
			}
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_servicedistance').disabled = false;
				fetch_js_object('cb_servicedistance').checked = false;
				toggle_show('toggleradiusservice');
			}
			
			if (force)
			{
				fetch_js_object('cb_servicelocation').checked = true;
			}
		}
		else
		{
			fetch_js_object('rb_serviceregionfrom').disabled = true;
			fetch_js_object('rb_serviceregionto').disabled = true;
			fetch_js_object('rb_serviceregionin').disabled = true;
			fetch_js_object('cb_servicelocation').disabled = false;
			fetch_js_object('servicecountryto').disabled = true;
			fetch_js_object('servicecountryin').disabled = true;
			fetch_js_object('serviceregionin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_servicedistance').disabled = true;
				fetch_js_object('serviceradius').disabled = true;
				fetch_js_object('serviceradiuszip').disabled = true;
				toggle_hide('toggleradiusservice');
			}
		}
	}
	else if (inputgroup == 'region_product')
	{
		if (checkbool)
		{
			fetch_js_object('rb_productregionfrom').disabled = false;
			fetch_js_object('rb_productregionto').disabled = false;
			fetch_js_object('rb_productregionin').disabled = false;
			fetch_js_object('cb_productlocation').checked = false;
			fetch_js_object('cb_productlocation').disabled = true;
			
			if (fetch_js_object('rb_productregionfrom').checked == true)
			{
				fetch_js_object('productregionin').disabled = false;
				fetch_js_object('productcountryto').disabled = true;
				fetch_js_object('productcountryin').disabled = true;
			}
			else if (fetch_js_object('rb_productregionto').checked == true)
			{
				fetch_js_object('productregionin').disabled = true;
				fetch_js_object('productcountryto').disabled = false;
				fetch_js_object('productcountryin').disabled = true;
			}
			else if (fetch_js_object('rb_productregionin').checked == true)
			{
				fetch_js_object('productregionin').disabled = true;
				fetch_js_object('productcountryto').disabled = true;
				fetch_js_object('productcountryin').disabled = false;
			}
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_productdistance').disabled = false;
				fetch_js_object('cb_productdistance').checked = false;
				toggle_show('toggleradiusproduct');
			}
			
			if (force)
			{
				fetch_js_object('cb_productlocation').checked = true;
			}
		}
		else
		{
			fetch_js_object('rb_productregionfrom').disabled = true;
			fetch_js_object('rb_productregionto').disabled = true;
			fetch_js_object('rb_productregionin').disabled = true;
			fetch_js_object('cb_productlocation').disabled = false;
			fetch_js_object('productcountryto').disabled = true;
			fetch_js_object('productcountryin').disabled = true;
			fetch_js_object('productregionin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_productdistance').disabled = true;
				fetch_js_object('productradius').disabled = true;
				fetch_js_object('productradiuszip').disabled = true;
				toggle_hide('toggleradiusproduct');
			}
		}
	}
	else if (inputgroup == 'region_experts')
	{
		if (checkbool)
		{
			fetch_js_object('rb_expertregionfrom').disabled = false;
			fetch_js_object('rb_expertregionto').disabled = false;
			fetch_js_object('rb_expertregionin').disabled = false;
			fetch_js_object('cb_expertlocation').checked = false;
			fetch_js_object('cb_expertlocation').disabled = true;
			
			if (fetch_js_object('rb_expertregionfrom').checked == true)
			{
				fetch_js_object('expertregionin').disabled = false;
				fetch_js_object('expertcountryto').disabled = true;
				fetch_js_object('expertcountryin').disabled = true;
			}
			else if (fetch_js_object('rb_expertregionto').checked == true)
			{
				fetch_js_object('expertregionin').disabled = true;
				fetch_js_object('expertcountryto').disabled = false;
				fetch_js_object('expertcountryin').disabled = true;
			}
			else if (fetch_js_object('rb_expertregionin').checked == true)
			{
				fetch_js_object('expertregionin').disabled = true;
				fetch_js_object('expertcountryto').disabled = true;
				fetch_js_object('expertcountryin').disabled = false;
			}
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_expertdistance').disabled = false;
				fetch_js_object('cb_expertdistance').checked = false;
				toggle_show('toggleradiusexperts');
			}
			
			if (force)
			{
				fetch_js_object('cb_expertlocation').checked = true;
			}
		}
		else
		{
			fetch_js_object('rb_expertregionfrom').disabled = true;
			fetch_js_object('rb_expertregionto').disabled = true;
			fetch_js_object('rb_expertregionin').disabled = true;
			fetch_js_object('cb_expertlocation').disabled = false;
			fetch_js_object('expertcountryto').disabled = true;
			fetch_js_object('expertcountryin').disabled = true;
			fetch_js_object('expertregionin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_expertdistance').disabled = true;
				fetch_js_object('expertradius').disabled = true;
				fetch_js_object('expertradiuszip').disabled = true;
				toggle_hide('toggleradiusexperts');
			}
		}
	}
	else if (inputgroup == 'radio_serviceregion')
	{
		if (fetch_js_object('rb_serviceregionfrom').checked == true)
		{
			fetch_js_object('serviceregionin').disabled = false;
			fetch_js_object('servicecountryto').disabled = true;
			fetch_js_object('servicecountryin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_servicedistance').disabled = false;
				toggle_show('toggleradiusservice');
			}
		}
		else if (fetch_js_object('rb_serviceregionto').checked == true)
		{
			fetch_js_object('serviceregionin').disabled = true;
			fetch_js_object('servicecountryto').disabled = false;
			fetch_js_object('servicecountryin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_servicedistance').disabled = false;
				toggle_show('toggleradiusservice');
			}
		}
		else if (fetch_js_object('rb_serviceregionin').checked == true)
		{
			fetch_js_object('serviceregionin').disabled = true;
			fetch_js_object('servicecountryto').disabled = true;
			fetch_js_object('servicecountryin').disabled = false;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_servicedistance').disabled = false;
				toggle_show('toggleradiusservice');
			}
		}	
	}
	else if (inputgroup == 'radio_productregion')
	{
		if (fetch_js_object('rb_productregionfrom').checked == true)
		{
			fetch_js_object('productregionin').disabled = false;
			fetch_js_object('productcountryto').disabled = true;
			fetch_js_object('productcountryin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_productdistance').disabled = false;
				toggle_show('toggleradiusproduct');
			}
		}
		else if (fetch_js_object('rb_productregionto').checked == true)
		{
			fetch_js_object('productregionin').disabled = true;
			fetch_js_object('productcountryto').disabled = false;
			fetch_js_object('productcountryin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_productdistance').disabled = false;
				toggle_show('toggleradiusproduct');
			}
		}
		else if (fetch_js_object('rb_productregionin').checked == true)
		{
			fetch_js_object('productregionin').disabled = true;
			fetch_js_object('productcountryto').disabled = true;
			fetch_js_object('productcountryin').disabled = false;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_productdistance').disabled = false;
				toggle_show('toggleradiusproduct');
			}
		}	
	}
	else if (inputgroup == 'radio_expertregion')
	{
		if (fetch_js_object('rb_expertregionfrom').checked == true)
		{
			fetch_js_object('expertregionin').disabled = false;
			fetch_js_object('expertcountryto').disabled = true;
			fetch_js_object('expertcountryin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_expertdistance').disabled = false;
				toggle_show('toggleradiusexperts');
			}
		}
		else if (fetch_js_object('rb_expertregionto').checked == true)
		{
			fetch_js_object('expertregionin').disabled = true;
			fetch_js_object('expertcountryto').disabled = false;
			fetch_js_object('expertcountryin').disabled = true;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_expertdistance').disabled = false;
				toggle_show('toggleradiusexperts');
			}
		}
		else if (fetch_js_object('rb_expertregionin').checked == true)
		{
			fetch_js_object('expertregionin').disabled = true;
			fetch_js_object('expertcountryto').disabled = true;
			fetch_js_object('expertcountryin').disabled = false;
			
			if (DISTANCE == 1)
			{
				fetch_js_object('cb_expertdistance').disabled = false;
				toggle_show('toggleradiusexperts');
			}
		}	
	}
	else if (inputgroup == 'expertoptions')
	{
		if (checkbool)
		{
			fetch_js_object('css_islogd1_1').className = 'black';
			fetch_js_object('css_images3_1').className = 'black';
			fetch_js_object('islogd1_1').disabled = false;
			fetch_js_object('images3_1').disabled = false;
			if (force)
			{
				fetch_js_object('cb_expertoptions').checked = true;
			}			
		}
		else
		{
			fetch_js_object('css_islogd1_1').className = 'gray';
			fetch_js_object('css_images3_1').className = 'gray';
			fetch_js_object('islogd1_1').disabled = true;
			fetch_js_object('images3_1').disabled = true;
		}
	}
	else if (inputgroup == 'servicelisted')
	{
		if (checkbool)
		{
			fetch_js_object('serviceendstart').disabled = false;
			fetch_js_object('serviceendstart_filter').disabled = false;
			if (force)
			{
				fetch_js_object('cb_servicelisted').checked = true;
			}			
		}
		else
		{
			fetch_js_object('serviceendstart').disabled = true;
			fetch_js_object('serviceendstart_filter').disabled = true;
		}
	}
	else if (inputgroup == 'productlisted')
	{
		if (checkbool)
		{
			fetch_js_object('productendstart').disabled = false;
			fetch_js_object('productendstart_filter').disabled = false;
			if (force)
			{
				fetch_js_object('cb_productlisted').checked = true;
			}			
		}
		else
		{
			fetch_js_object('productendstart').disabled = true;
			fetch_js_object('productendstart_filter').disabled = true;
		}
	}
	else if (inputgroup == 'awardrange')
	{
		if (checkbool)
		{
			fetch_js_object('projectrange').disabled = false;
			if (force)
			{
				fetch_js_object('cb_awardrange').checked = true;
			}			
		}
		else
		{
			fetch_js_object('projectrange').disabled = true;
		}
	}
	else if (inputgroup == 'servicebidrange')
	{
		if (checkbool)
		{
			fetch_js_object('servicebidrange').disabled = false;
			if (force)
			{
				fetch_js_object('cb_servicebidrange').checked = true;
			}			
		}
		else
		{
			fetch_js_object('servicebidrange').disabled = true;
		}
	}
	else if (inputgroup == 'productbidrange')
	{
		if (checkbool)
		{
			fetch_js_object('productbidrange').disabled = false;
			if (force)
			{
				fetch_js_object('cb_productbidrange').checked = true;
			}			
		}
		else
		{
			fetch_js_object('productbidrange').disabled = true;
		}
	}
	else if (inputgroup == 'servicesearchuser')
	{
		if (checkbool)
		{
			fetch_js_object('servicesearchuser').disabled = false;
			fetch_js_object('serviceexactname').disabled = false;
			if (force)
			{
				fetch_js_object('cb_servicesearchuser').checked = true;
			}			
		}
		else
		{
			fetch_js_object('servicesearchuser').disabled = true;
			fetch_js_object('serviceexactname').disabled = true;
		}
	}
	else if (inputgroup == 'productsearchuser')
	{
		if (checkbool)
		{
			fetch_js_object('productsearchuser').disabled = false;
			fetch_js_object('productexactname').disabled = false;
			if (force)
			{
				fetch_js_object('cb_productsearchuser').checked = true;
			}			
		}
		else
		{
			fetch_js_object('productsearchuser').disabled = true;
			fetch_js_object('productexactname').disabled = true;
		}
	}
	else if (inputgroup == 'ratingrange')
	{
		if (checkbool)
		{
			fetch_js_object('rating').disabled = false;
			if (force)
			{
				fetch_js_object('cb_ratingrange').checked = true;
			}			
		}
		else
		{
			fetch_js_object('rating').disabled = true;
		}
	}
	else if (inputgroup == 'listingtype')
	{
		if (checkbool)
		{
			fetch_js_object('auctiontype').disabled = false;
			if (force)
			{
				fetch_js_object('cb_listingtype').checked = true;
			}			
		}
		else
		{
			fetch_js_object('auctiontype').disabled = true;
		}
	}
	else if (inputgroup == 'productlistingtype')
	{
		if (checkbool)
		{
			fetch_js_object('css_cb_buyingformat2_1').className = 'black';
			fetch_js_object('css_cb_buyingformat2_2').className = 'black';
			fetch_js_object('css_cb_buyingformat2_3').className = 'black';
			fetch_js_object('css_cb_buyingformat2_4').className = 'black';
			fetch_js_object('cb_productlistingtype').disabled = false;
			fetch_js_object('cb_buyingformat2_1').disabled = false;
			fetch_js_object('cb_buyingformat2_2').disabled = false;
			fetch_js_object('cb_buyingformat2_3').disabled = false;
			fetch_js_object('cb_buyingformat2_4').disabled = false;
			fetch_js_object('css_cb_buyingformat2_6').className = 'black';
			fetch_js_object('cb_buyingformat2_6').disabled = false;
			
			if (force)
			{
				fetch_js_object('cb_productlistingtype').checked = true;
			}			
		}
		else
		{
			fetch_js_object('css_cb_buyingformat2_1').className = 'gray';
			fetch_js_object('css_cb_buyingformat2_2').className = 'gray';
			fetch_js_object('css_cb_buyingformat2_3').className = 'gray';
			fetch_js_object('css_cb_buyingformat2_4').className = 'gray';
			fetch_js_object('cb_productlistingtype').disabled = false;
			fetch_js_object('cb_buyingformat2_1').disabled = true;
			fetch_js_object('cb_buyingformat2_2').disabled = true;
			fetch_js_object('cb_buyingformat2_3').disabled = true;
			fetch_js_object('cb_buyingformat2_4').disabled = true;
			fetch_js_object('css_cb_buyingformat2_6').className = 'gray';
			fetch_js_object('cb_buyingformat2_6').disabled = true;
		}
	}
	else if (inputgroup == 'servicelistingtype')
	{
		if (checkbool)
		{
			fetch_js_object('css_cb_buyingformat1_1').className = 'black';
			fetch_js_object('css_cb_buyingformat1_2').className = 'black';
			fetch_js_object('css_cb_buyingformat1_3').className = 'black';
			fetch_js_object('css_cb_buyingformat1_4').className = 'black';
			fetch_js_object('cb_servicelistingtype').disabled = false;
			fetch_js_object('cb_buyingformat1_1').disabled = false;
			fetch_js_object('cb_buyingformat1_2').disabled = false;
			fetch_js_object('cb_buyingformat1_3').disabled = false;
			fetch_js_object('cb_buyingformat1_4').disabled = false;
			if (force)
			{
				fetch_js_object('cb_servicelistingtype').checked = true;
			}			
		}
		else
		{
			fetch_js_object('css_cb_buyingformat1_1').className = 'gray';
			fetch_js_object('css_cb_buyingformat1_2').className = 'gray';
			fetch_js_object('css_cb_buyingformat1_3').className = 'gray';
			fetch_js_object('css_cb_buyingformat1_4').className = 'gray';
			fetch_js_object('cb_servicelistingtype').disabled = false;
			fetch_js_object('cb_buyingformat1_1').disabled = true;
			fetch_js_object('cb_buyingformat1_2').disabled = true;
			fetch_js_object('cb_buyingformat1_3').disabled = true;
			fetch_js_object('cb_buyingformat1_4').disabled = true;
		}
	}
	else if (inputgroup == 'serviceoptions')
	{
		if (checkbool)
		{
			fetch_js_object('css_budget1_1').className = 'black';
			fetch_js_object('css_pboard1_1').className = 'black';
			fetch_js_object('budget1_1').disabled = false;
			fetch_js_object('pboard1_1').disabled = false;
			if (ESCROW == '1')
			{
				fetch_js_object('css_escrow1_1').className = 'black';
				fetch_js_object('escrow1_1').disabled = false;
			}
			if (force)
			{
				fetch_js_object('cb_serviceoptions').checked = true;
			}			
		}
		else
		{
			fetch_js_object('css_budget1_1').className = 'gray';
			fetch_js_object('css_pboard1_1').className = 'gray';
			fetch_js_object('budget1_1').disabled = true;
			fetch_js_object('pboard1_1').disabled = true;
			if (ESCROW == '1')
			{
				fetch_js_object('css_escrow1_1').className = 'gray';
				fetch_js_object('escrow1_1').disabled = true;
			}
		}
	}
	else if (inputgroup == 'productoptions')
	{
		if (checkbool)
		{
			fetch_js_object('css_buynow2_1').className = 'black';
			fetch_js_object('css_images2_1').className = 'black';
			fetch_js_object('css_pboard2_1').className = 'black';
			fetch_js_object('css_freesh2_1').className = 'black';
			fetch_js_object('css_laslot2_1').className = 'black';
			fetch_js_object('buynow2_1').disabled = false;
			fetch_js_object('images2_1').disabled = false;
			fetch_js_object('pboard2_1').disabled = false;
			fetch_js_object('freesh2_1').disabled = false;
			fetch_js_object('laslot2_1').disabled = false;
			if (ESCROW == '1')
			{
				fetch_js_object('css_escrow2_1').className = 'black';
				fetch_js_object('escrow2_1').disabled = false;
			}
			if (force)
			{
				fetch_js_object('cb_productoptions').checked = true;
			}			
		}
		else
		{
			fetch_js_object('css_buynow2_1').className = 'gray';
			fetch_js_object('css_images2_1').className = 'gray';
			fetch_js_object('css_pboard2_1').className = 'gray';
			fetch_js_object('css_freesh2_1').className = 'gray';
			fetch_js_object('css_laslot2_1').className = 'gray';
			fetch_js_object('buynow2_1').disabled = true;
			fetch_js_object('images2_1').disabled = true;
			fetch_js_object('pboard2_1').disabled = true;
			fetch_js_object('freesh2_1').disabled = true;
			fetch_js_object('laslot2_1').disabled = true;
			if (ESCROW == '1')
			{
				fetch_js_object('css_escrow2_1').className = 'gray';
				fetch_js_object('escrow2_1').disabled = true;
			}
		}
	}
	else if (inputgroup == 'pricerange')
	{
		if (checkbool)
		{
			fetch_js_object('frompricerange').disabled = false;
			fetch_js_object('topricerange').disabled = false;
			if (force)
			{
				fetch_js_object('cb_pricerange').checked = true;
			}
		}
		else
		{
			fetch_js_object('frompricerange').disabled = true;
			fetch_js_object('topricerange').disabled = true;
		}
	}
}