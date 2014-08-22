<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2014 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* Distance calculation class to perform the majority of distance and radius calculation functions in ILance.
*
* The current state of the distance calculation server currently supports:
* 
* a) Canada
* b) UK
* c) United States
* d) Netherlands
* e) Australia
* f) Germany
* g) Poland
* h) Spain
* i) India
* j) Belgium
* k) France
* l) Italy
* m) Japan
* n) Kenya
* o) Hungary
* p) Morocco
* q) Nigeria
* r) Romania
* s) Turkey
* t) Brazil
*
* @notes        Actual distance "datas" can be purchased online from a reliable geo-code solution provider.
*               DB Table Fields required are (3): ZIPCode, Longitude & Latitude
*
* @package      iLance\Distance
* @version      4.0.0.8059
* @author       ILance
*/
class distance
{
        /**
        * Country ids to short form identifiers accepted in distance calculation
        */
        var $countries = array(
                '262' => 'UK',
                '330' => 'CAN',
                '500' => 'USA',
                '114' => 'NL',
		'307' => 'AUS',
                '361' => 'DE',
                '130' => 'PL',
		'156' => 'SP',
                '375' => 'IN',
                '315' => 'BE',
		'357' => 'FR',
		'381' => 'IT',
		'384' => 'JP',
		'386' => 'KE',
		'373' => 'HU',
		'110' => 'MA',
		'120' => 'NG',
		'134' => 'RO',
		'170' => 'TR',
                '323' => 'BR'
        );
        /**
        * Accepted country ids allowed for distance calculation operations
        */
        var $accepted_countries = array(
                '262',
                '330',
                '500',
                '114',
		'307',
                '361',
                '130',
		'156',
                '375',
                '315',
		'357',
		'381',
		'384',
		'386',
		'373',
		'110',
		'120',
		'134',
		'170',
                '323'
        );
	/**
	* Lables for the AdminCP > Distance area
	*/
	var $distance_titles = array(
		'CAN' => 'Canada',
                'USA' => 'United States',
                'UK' => 'United Kingdom',
                'NL' => 'Netherlands',
		'AUS' => 'Australia',
                'DE' => 'Germany',
                'PL' => 'Poland',
		'SP' => 'Spain',
                'IN' => 'India',
                'BE' => 'Belgium',
		'FR' => 'France',
		'IT' => 'Italy',
		'JP' => 'Japan',
		'KE' => 'Kenya',
		'HU' => 'Hungary',
		'MA' => 'Morocco',
		'NG' => 'Nigeria',
		'RO' => 'Romania',
		'TR' => 'Turkey',
                'BR' => 'Brazil'
	);
	/**
	* Cities for the AdminCP > Distance area
	*/
	var $distance_cities = array(
		'CAN' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'USA' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'UK' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'NL' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'AUS' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'DE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'PL' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'SP' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'IN' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'BE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'FR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'IT' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'JP' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'KE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'HU' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'MA' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'NG' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'RO' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'TR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'BR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />'
	);
	/**
	* States for the AdminCP > Distance area
	*/
	var $distance_states = array(
		'CAN' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'USA' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'UK' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'NL' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'AUS' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'DE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'PL' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'SP' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'IN' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
                'BE' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'FR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'IT' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'JP' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'KE' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'HU' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'MA' => '<img src="../images/default/unchecked.gif" border="0" alt="" id="" />',
		'NG' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'RO' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
		'TR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />',
                'BR' => '<img src="../images/default/checked.gif" border="0" alt="" id="" />'
	);
	/**
	* Table counts for the AdminCP > Distance area
	*/
	var $distance_count = array(
		'CAN' => '774,014',
                'USA' => '70,706',
                'UK' => '1,969,257',
                'NL' => '435,296',
		'AUS' => '16,079',
                'DE' => '16,375',
                'PL' => '21,987',
		'SP' => '54,116',
                'IN' => '14,568',
                'BE' => '3,778',
		'FR' => '39,069',
		'IT' => '17,965',
		'JP' => '83,289',
		'KE' => '0',
		'HU' => '0',
		'MA' => '6,610',
		'NG' => '3,160',
		'RO' => '37,072',
		'TR' => '3,206',
                'BR' => '764,090'
	);
        /**
        * Database tables to call based on a distance operation
        */
        var $dbtables = array(
                'CAN' => 'distance_canada',
                'USA' => 'distance_usa',
                'UK' => 'distance_uk',
                'NL' => 'distance_nl',
		'AUS' => 'distance_au',
                'DE' => 'distance_de',
                'PL' => 'distance_pl',
		'SP' => 'distance_sp',
                'IN' => 'distance_in',
                'BE' => 'distance_be',
		'FR' => 'distance_fr',
		'IT' => 'distance_it',
		'JP' => 'distance_jp',
		'KE' => 'distance_ke',
		'HU' => 'distance_hu',
		'MA' => 'distance_ma',
		'NG' => 'distance_ng',
		'RO' => 'distance_ro',
		'TR' => 'distance_tr',
                'BR' => 'distance_br',
		// when searching from Canada to x country
                'CANUSA' => array('distance_canada', 'distance_usa'),
		'CANUK' => array('distance_canada', 'distance_uk'),
                'CANNL' => array('distance_canada', 'distance_nl'),
		'CANAUS' => array('distance_canada', 'distance_au'),
                'CANDE' => array('distance_canada', 'distance_de'),
                'CANPL' => array('distance_canada', 'distance_pl'),
		'CANSP' => array('distance_canada', 'distance_sp'),
                'CANIN' => array('distance_canada', 'distance_in'),
                'CANBE' => array('distance_canada', 'distance_be'),
		'CANFR' => array('distance_canada', 'distance_fr'),
		'CANIT' => array('distance_canada', 'distance_it'),
		'CANJP' => array('distance_canada', 'distance_jp'),
		'CANKE' => array('distance_canada', 'distance_ke'),
		'CANHU' => array('distance_canada', 'distance_hu'),
		'CANMA' => array('distance_canada', 'distance_ma'),
		'CANNG' => array('distance_canada', 'distance_ng'),
		'CANRO' => array('distance_canada', 'distance_ro'),
		'CANTR' => array('distance_canada', 'distance_tr'),
                'CANBR' => array('distance_canada', 'distance_br'),
		// when searching from US to x country
		'USACAN' => array('distance_usa', 'distance_canada'),
		'USAUK' => array('distance_usa', 'distance_uk'),
                'USANL' => array('distance_usa', 'distance_nl'),
		'USAAUS' => array('distance_usa', 'distance_au'),
                'USADE' => array('distance_usa', 'distance_de'),
                'USAPL' => array('distance_usa', 'distance_pl'),
		'USASP' => array('distance_usa', 'distance_sp'),
                'USAIN' => array('distance_usa', 'distance_in'),
                'USABE' => array('distance_usa', 'distance_be'),
		'USAFR' => array('distance_usa', 'distance_fr'),
		'USAIT' => array('distance_usa', 'distance_it'),
		'USAJP' => array('distance_usa', 'distance_jp'),
		'USAKE' => array('distance_usa', 'distance_ke'),
		'USAHU' => array('distance_usa', 'distance_hu'),
		'USAMA' => array('distance_usa', 'distance_ma'),
		'USANG' => array('distance_usa', 'distance_ng'),
		'USARO' => array('distance_usa', 'distance_ro'),
		'USATR' => array('distance_usa', 'distance_tr'),
                'USABR' => array('distance_usa', 'distance_br'),
		// when searching from UK to x country
		'UKCAN' => array('distance_uk', 'distance_canada'),
		'UKUSA' => array('distance_uk', 'distance_usa'),
                'UKNL' => array('distance_uk', 'distance_nl'),
		'UKAUS' => array('distance_uk', 'distance_au'),
                'UKDE' => array('distance_uk', 'distance_de'),
                'UKPL' => array('distance_uk', 'distance_pl'),
		'UKSP' => array('distance_uk', 'distance_sp'),
                'UKIN' => array('distance_uk', 'distance_in'),
                'UKBE' => array('distance_uk', 'distance_be'),
		'UKFR' => array('distance_uk', 'distance_fr'),
		'UKIT' => array('distance_uk', 'distance_it'),
		'UKJP' => array('distance_uk', 'distance_jp'),
		'UKKE' => array('distance_uk', 'distance_ke'),
		'UKHU' => array('distance_uk', 'distance_hu'),
		'UKMA' => array('distance_uk', 'distance_ma'),
		'UKNG' => array('distance_uk', 'distance_ng'),
		'UKRO' => array('distance_uk', 'distance_ro'),
		'UKTR' => array('distance_uk', 'distance_tr'),
                'UKBR' => array('distance_uk', 'distance_br'),
		// when searching from NL to x country
                'NLCAN' => array('distance_nl', 'distance_canada'),
		'NLUSA' => array('distance_nl', 'distance_usa'),
                'NLUK' => array('distance_nl', 'distance_uk'),
		'NLAUS' => array('distance_nl', 'distance_au'),
                'NLDE' => array('distance_nl', 'distance_de'),
                'NLPL' => array('distance_nl', 'distance_pl'),
		'NLSP' => array('distance_nl', 'distance_sp'),
                'NLIN' => array('distance_nl', 'distance_in'),
                'NLBE' => array('distance_nl', 'distance_be'),
		'NLFR' => array('distance_nl', 'distance_fr'),
		'NLIT' => array('distance_nl', 'distance_it'),
		'NLJP' => array('distance_nl', 'distance_jp'),
		'NLKE' => array('distance_nl', 'distance_ke'),
		'NLHU' => array('distance_nl', 'distance_hu'),
		'NLMA' => array('distance_nl', 'distance_ma'),
		'NLNG' => array('distance_nl', 'distance_ng'),
		'NLRO' => array('distance_nl', 'distance_ro'),
		'NLTR' => array('distance_nl', 'distance_tr'),
                'NLBR' => array('distance_nl', 'distance_br'),
		// when searching from AUS to x country
		'AUSCAN' => array('distance_au', 'distance_canada'),
		'AUSUSA' => array('distance_au', 'distance_usa'),
                'AUSUK' => array('distance_au', 'distance_uk'),
		'AUSNL' => array('distance_au', 'distance_nl'),
                'AUSDE' => array('distance_au', 'distance_de'),
                'AUSPL' => array('distance_au', 'distance_pl'),
		'AUSSP' => array('distance_au', 'distance_sp'),
                'AUSIN' => array('distance_au', 'distance_in'),
                'AUSBE' => array('distance_au', 'distance_be'),
		'AUSFR' => array('distance_au', 'distance_fr'),
		'AUSIT' => array('distance_au', 'distance_it'),
		'AUSJP' => array('distance_au', 'distance_jp'),
		'AUSKE' => array('distance_au', 'distance_ke'),
		'AUSHU' => array('distance_au', 'distance_hu'),
		'AUSMA' => array('distance_au', 'distance_ma'),
		'AUSNG' => array('distance_au', 'distance_ng'),
		'AUSRO' => array('distance_au', 'distance_ro'),
		'AUSTR' => array('distance_au', 'distance_tr'),
                'AUSBR' => array('distance_au', 'distance_br'),
                // when searching from DE to x country
		'DECAN' => array('distance_de', 'distance_canada'),
		'DEUSA' => array('distance_de', 'distance_usa'),
                'DEUK' => array('distance_de', 'distance_uk'),
		'DENL' => array('distance_de', 'distance_nl'),
                'DEAUS' => array('distance_de', 'distance_au'),
                'DEPL' => array('distance_de', 'distance_pl'),
		'DESP' => array('distance_de', 'distance_sp'),
                'DEIN' => array('distance_de', 'distance_in'),
                'DEBE' => array('distance_de', 'distance_be'),
		'DEFR' => array('distance_de', 'distance_fr'),
		'DEIT' => array('distance_de', 'distance_it'),
		'DEJP' => array('distance_de', 'distance_jp'),
		'DEKE' => array('distance_de', 'distance_ke'),
		'DEHU' => array('distance_de', 'distance_hu'),
		'DEMA' => array('distance_de', 'distance_ma'),
		'DENG' => array('distance_de', 'distance_ng'),
		'DERO' => array('distance_de', 'distance_ro'),
		'DETR' => array('distance_de', 'distance_tr'),
                'DEBR' => array('distance_de', 'distance_br'),
                // when searching from PL to x country
		'PLCAN' => array('distance_pl', 'distance_canada'),
		'PLUSA' => array('distance_pl', 'distance_usa'),
                'PLUK' => array('distance_pl', 'distance_uk'),
		'PLNL' => array('distance_pl', 'distance_nl'),
                'PLAUS' => array('distance_pl', 'distance_au'),
                'PLDE' => array('distance_pl', 'distance_de'),
		'PLSP' => array('distance_pl', 'distance_sp'),
                'PLIN' => array('distance_pl', 'distance_in'),
                'PLBE' => array('distance_pl', 'distance_be'),
		'PLFR' => array('distance_pl', 'distance_fr'),
		'PLIT' => array('distance_pl', 'distance_it'),
		'PLJP' => array('distance_pl', 'distance_jp'),
		'PLKE' => array('distance_pl', 'distance_ke'),
		'PLHU' => array('distance_pl', 'distance_hu'),
		'PLMA' => array('distance_pl', 'distance_ma'),
		'PLNG' => array('distance_pl', 'distance_ng'),
		'PLRO' => array('distance_pl', 'distance_ro'),
		'PLTR' => array('distance_pl', 'distance_tr'),
                'PLBR' => array('distance_pl', 'distance_br'),
		// when searching from SP to x country
		'SPCAN' => array('distance_sp', 'distance_canada'),
		'SPUSA' => array('distance_sp', 'distance_usa'),
                'SPUK' => array('distance_sp', 'distance_uk'),
		'SPNL' => array('distance_sp', 'distance_nl'),
                'SPAUS' => array('distance_sp', 'distance_au'),
                'SPDE' => array('distance_sp', 'distance_de'),
		'SPPL' => array('distance_sp', 'distance_pl'),
                'SPIN' => array('distance_sp', 'distance_in'),
                'SPBE' => array('distance_sp', 'distance_be'),
		'SPFR' => array('distance_sp', 'distance_fr'),
		'SPIT' => array('distance_sp', 'distance_it'),
		'SPJP' => array('distance_sp', 'distance_jp'),
		'SPKE' => array('distance_sp', 'distance_ke'),
		'SPHU' => array('distance_sp', 'distance_hu'),
		'SPMA' => array('distance_sp', 'distance_ma'),
		'SPNG' => array('distance_sp', 'distance_ng'),
		'SPRO' => array('distance_sp', 'distance_ro'),
		'SPTR' => array('distance_sp', 'distance_tr'),
                'SPBR' => array('distance_sp', 'distance_br'),
                // when searching from IN to x country
		'INCAN' => array('distance_in', 'distance_canada'),
		'INUSA' => array('distance_in', 'distance_usa'),
                'INUK' => array('distance_in', 'distance_uk'),
		'INNL' => array('distance_in', 'distance_nl'),
                'INAUS' => array('distance_in', 'distance_au'),
                'INDE' => array('distance_in', 'distance_de'),
		'INPL' => array('distance_in', 'distance_pl'),
                'INSP' => array('distance_in', 'distance_sp'),
                'INBE' => array('distance_in', 'distance_be'),
		'INFR' => array('distance_in', 'distance_fr'),
		'INIT' => array('distance_in', 'distance_it'),
		'INJP' => array('distance_in', 'distance_jp'),
		'INKE' => array('distance_in', 'distance_ke'),
		'INHU' => array('distance_in', 'distance_hu'),
		'INMA' => array('distance_in', 'distance_ma'),
		'INNG' => array('distance_in', 'distance_ng'),
		'INRO' => array('distance_in', 'distance_ro'),
		'INTR' => array('distance_in', 'distance_tr'),
                'INBR' => array('distance_in', 'distance_br'),
                // when searching from BE to x country
		'BECAN' => array('distance_be', 'distance_canada'),
                'BEUSA' => array('distance_be', 'distance_usa'),
                'BEUK' => array('distance_be', 'distance_uk'),
                'BENL' => array('distance_be', 'distance_nl'),
                'BEAUS' => array('distance_be', 'distance_au'),
                'BEDE' => array('distance_be', 'distance_de'),
                'BEPL' => array('distance_be', 'distance_pl'),
                'BESP' => array('distance_be', 'distance_sp'),
		'BEFR' => array('distance_be', 'distance_fr'),
		'BEIN' => array('distance_be', 'distance_in'),
		'BEIT' => array('distance_be', 'distance_it'),
		'BEJP' => array('distance_be', 'distance_jp'),
		'BEKE' => array('distance_be', 'distance_ke'),
		'BEHU' => array('distance_be', 'distance_hu'),
		'BEMA' => array('distance_be', 'distance_ma'),
		'BENG' => array('distance_be', 'distance_ng'),
		'BERO' => array('distance_be', 'distance_ro'),
		'BETR' => array('distance_be', 'distance_tr'),
                'BEBR' => array('distance_be', 'distance_br'),
		// when searching from FR to x country
		'FRCAN' => array('distance_fr', 'distance_canada'),
                'FRUSA' => array('distance_fr', 'distance_usa'),
                'FRUK' => array('distance_fr', 'distance_uk'),
                'FRNL' => array('distance_fr', 'distance_nl'),
                'FRAUS' => array('distance_fr', 'distance_au'),
                'FRDE' => array('distance_fr', 'distance_de'),
                'FRPL' => array('distance_fr', 'distance_pl'),
                'FRSP' => array('distance_fr', 'distance_sp'),
		'FRBE' => array('distance_fr', 'distance_be'),
		'FRIN' => array('distance_fr', 'distance_in'),
		'FRIT' => array('distance_fr', 'distance_it'),
		'FRJP' => array('distance_fr', 'distance_jp'),
		'FRKE' => array('distance_fr', 'distance_ke'),
		'FRHU' => array('distance_fr', 'distance_hu'),
		'FRMA' => array('distance_fr', 'distance_ma'),
		'FRNG' => array('distance_fr', 'distance_ng'),
		'FRRO' => array('distance_fr', 'distance_ro'),
		'FRTR' => array('distance_fr', 'distance_tr'),
                'FRBR' => array('distance_fr', 'distance_br'),
		// when searching from IT to x country
		'ITCAN' => array('distance_it', 'distance_canada'),
                'ITUSA' => array('distance_it', 'distance_usa'),
                'ITUK' => array('distance_it', 'distance_uk'),
                'ITNL' => array('distance_it', 'distance_nl'),
                'ITAUS' => array('distance_it', 'distance_au'),
                'ITDE' => array('distance_it', 'distance_de'),
                'ITPL' => array('distance_it', 'distance_pl'),
                'ITSP' => array('distance_it', 'distance_sp'),
		'ITBE' => array('distance_it', 'distance_be'),
		'ITIN' => array('distance_it', 'distance_in'),
		'ITFR' => array('distance_it', 'distance_fr'),
		'ITJP' => array('distance_it', 'distance_jp'),
		'ITKE' => array('distance_it', 'distance_ke'),
		'ITHU' => array('distance_it', 'distance_hu'),
		'ITMA' => array('distance_it', 'distance_ma'),
		'ITNG' => array('distance_it', 'distance_ng'),
		'ITRO' => array('distance_it', 'distance_ro'),
		'ITTR' => array('distance_it', 'distance_tr'),
                'ITBR' => array('distance_it', 'distance_br'),
		// when searching from JP to x country
		'JPCAN' => array('distance_jp', 'distance_canada'),
                'JPUSA' => array('distance_jp', 'distance_usa'),
                'JPUK' => array('distance_jp', 'distance_uk'),
                'JPNL' => array('distance_jp', 'distance_nl'),
                'JPAUS' => array('distance_jp', 'distance_au'),
                'JPDE' => array('distance_jp', 'distance_de'),
                'JPPL' => array('distance_jp', 'distance_pl'),
                'JPSP' => array('distance_jp', 'distance_sp'),
		'JPBE' => array('distance_jp', 'distance_be'),
		'JPIN' => array('distance_jp', 'distance_in'),
		'JPFR' => array('distance_jp', 'distance_fr'),
		'JPIT' => array('distance_jp', 'distance_it'),
		'JPKE' => array('distance_jp', 'distance_ke'),
		'JPHU' => array('distance_jp', 'distance_hu'),
		'JPMA' => array('distance_jp', 'distance_ma'),
		'JPNG' => array('distance_jp', 'distance_ng'),
		'JPRO' => array('distance_jp', 'distance_ro'),
		'JPTR' => array('distance_jp', 'distance_tr'),
                'JPBR' => array('distance_jp', 'distance_br'),
		// when searching from KE to x country
		'KECAN' => array('distance_ke', 'distance_canada'),
                'KEUSA' => array('distance_ke', 'distance_usa'),
                'KEUK' => array('distance_ke', 'distance_uk'),
                'KENL' => array('distance_ke', 'distance_nl'),
                'KEAUS' => array('distance_ke', 'distance_au'),
                'KEDE' => array('distance_ke', 'distance_de'),
                'KEPL' => array('distance_ke', 'distance_pl'),
                'KESP' => array('distance_ke', 'distance_sp'),
		'KEBE' => array('distance_ke', 'distance_be'),
		'KEIN' => array('distance_ke', 'distance_in'),
		'KEFR' => array('distance_ke', 'distance_fr'),
		'KEIT' => array('distance_ke', 'distance_it'),
		'KEJP' => array('distance_ke', 'distance_jp'),
		'KEHU' => array('distance_ke', 'distance_hu'),
		'KEMA' => array('distance_ke', 'distance_ma'),
		'KENG' => array('distance_ke', 'distance_ng'),
		'KERO' => array('distance_ke', 'distance_ro'),
		'KETR' => array('distance_ke', 'distance_tr'),
                'KEBR' => array('distance_ke', 'distance_br'),
		// when searching from HU to x country
		'HUCAN' => array('distance_hu', 'distance_canada'),
                'HUUSA' => array('distance_hu', 'distance_usa'),
                'HUUK' => array('distance_hu', 'distance_uk'),
                'HUNL' => array('distance_hu', 'distance_nl'),
                'HUAUS' => array('distance_hu', 'distance_au'),
                'HUDE' => array('distance_hu', 'distance_de'),
                'HUPL' => array('distance_hu', 'distance_pl'),
                'HUSP' => array('distance_hu', 'distance_sp'),
		'HUBE' => array('distance_hu', 'distance_be'),
		'HUIN' => array('distance_hu', 'distance_in'),
		'HUFR' => array('distance_hu', 'distance_fr'),
		'HUIT' => array('distance_hu', 'distance_it'),
		'HUJP' => array('distance_hu', 'distance_jp'),
		'HUKE' => array('distance_hu', 'distance_ke'),
		'HUMA' => array('distance_hu', 'distance_ma'),
		'HUNG' => array('distance_hu', 'distance_ng'),
		'HURO' => array('distance_hu', 'distance_ro'),
		'HUTR' => array('distance_hu', 'distance_tr'),
                'HUBR' => array('distance_hu', 'distance_br'),
		// when searching from MA to x country
		'MACAN' => array('distance_ma', 'distance_canada'),
                'MAUSA' => array('distance_ma', 'distance_usa'),
                'MAUK' => array('distance_ma', 'distance_uk'),
                'MANL' => array('distance_ma', 'distance_nl'),
                'MAAUS' => array('distance_ma', 'distance_au'),
                'MADE' => array('distance_ma', 'distance_de'),
                'MAPL' => array('distance_ma', 'distance_pl'),
                'MASP' => array('distance_ma', 'distance_sp'),
		'MABE' => array('distance_ma', 'distance_be'),
		'MAIN' => array('distance_ma', 'distance_in'),
		'MAFR' => array('distance_ma', 'distance_fr'),
		'MAIT' => array('distance_ma', 'distance_it'),
		'MAJP' => array('distance_ma', 'distance_jp'),
		'MAKE' => array('distance_ma', 'distance_ke'),
		'MAHU' => array('distance_ma', 'distance_hu'),
		'MANG' => array('distance_ma', 'distance_ng'),
		'MARO' => array('distance_ma', 'distance_ro'),
		'MATR' => array('distance_ma', 'distance_tr'),
                'MABR' => array('distance_ma', 'distance_br'),
		// when searching from NG to x country
		'NGCAN' => array('distance_ng', 'distance_canada'),
                'NGUSA' => array('distance_ng', 'distance_usa'),
                'NGUK' => array('distance_ng', 'distance_uk'),
                'NGNL' => array('distance_ng', 'distance_nl'),
                'NGAUS' => array('distance_ng', 'distance_au'),
                'NGDE' => array('distance_ng', 'distance_de'),
                'NGPL' => array('distance_ng', 'distance_pl'),
                'NGSP' => array('distance_ng', 'distance_sp'),
		'NGBE' => array('distance_ng', 'distance_be'),
		'NGIN' => array('distance_ng', 'distance_in'),
		'NGFR' => array('distance_ng', 'distance_fr'),
		'NGIT' => array('distance_ng', 'distance_it'),
		'NGJP' => array('distance_ng', 'distance_jp'),
		'NGKE' => array('distance_ng', 'distance_ke'),
		'NGHU' => array('distance_ng', 'distance_hu'),
		'NGMA' => array('distance_ng', 'distance_ma'),
		'NGRO' => array('distance_ng', 'distance_ro'),
		'NGTR' => array('distance_ng', 'distance_tr'),
                'NGBR' => array('distance_ng', 'distance_br'),
		// when searching from RO to x country
		'ROCAN' => array('distance_ro', 'distance_canada'),
                'ROUSA' => array('distance_ro', 'distance_usa'),
                'ROUK' => array('distance_ro', 'distance_uk'),
                'RONL' => array('distance_ro', 'distance_nl'),
                'ROAUS' => array('distance_ro', 'distance_au'),
                'RODE' => array('distance_ro', 'distance_de'),
                'ROPL' => array('distance_ro', 'distance_pl'),
                'ROSP' => array('distance_ro', 'distance_sp'),
		'ROBE' => array('distance_ro', 'distance_be'),
		'ROIN' => array('distance_ro', 'distance_in'),
		'ROFR' => array('distance_ro', 'distance_fr'),
		'ROIT' => array('distance_ro', 'distance_it'),
		'ROJP' => array('distance_ro', 'distance_jp'),
		'ROKE' => array('distance_ro', 'distance_ke'),
		'ROHU' => array('distance_ro', 'distance_hu'),
		'ROMA' => array('distance_ro', 'distance_ma'),
		'RONG' => array('distance_ro', 'distance_ng'),
		'ROTR' => array('distance_ro', 'distance_tr'),
                'ROBR' => array('distance_ro', 'distance_br'),
		// when searching from TR to x country
		'TRCAN' => array('distance_tr', 'distance_canada'),
                'TRUSA' => array('distance_tr', 'distance_usa'),
                'TRUK' => array('distance_tr', 'distance_uk'),
                'TRNL' => array('distance_tr', 'distance_nl'),
                'TRAUS' => array('distance_tr', 'distance_au'),
                'TRDE' => array('distance_tr', 'distance_de'),
                'TRPL' => array('distance_tr', 'distance_pl'),
                'TRSP' => array('distance_tr', 'distance_sp'),
		'TRBE' => array('distance_tr', 'distance_be'),
		'TRIN' => array('distance_tr', 'distance_in'),
		'TRFR' => array('distance_tr', 'distance_fr'),
		'TRIT' => array('distance_tr', 'distance_it'),
		'TRJP' => array('distance_tr', 'distance_jp'),
		'TRKE' => array('distance_tr', 'distance_ke'),
		'TRHU' => array('distance_tr', 'distance_hu'),
		'TRMA' => array('distance_tr', 'distance_ma'),
		'TRNG' => array('distance_tr', 'distance_ng'),
		'TRRO' => array('distance_tr', 'distance_ro'),
                'TRBR' => array('distance_tr', 'distance_br'),
                // when searching from BR to x country
		'BRCAN' => array('distance_br', 'distance_canada'),
                'BRUSA' => array('distance_br', 'distance_usa'),
                'BRUK' => array('distance_br', 'distance_uk'),
                'BRNL' => array('distance_br', 'distance_nl'),
                'BRAUS' => array('distance_br', 'distance_au'),
                'BRDE' => array('distance_br', 'distance_de'),
                'BRPL' => array('distance_br', 'distance_pl'),
                'BRSP' => array('distance_br', 'distance_sp'),
		'BRBE' => array('distance_br', 'distance_be'),
		'BRIN' => array('distance_br', 'distance_in'),
		'BRFR' => array('distance_br', 'distance_fr'),
		'BRIT' => array('distance_br', 'distance_it'),
		'BRJP' => array('distance_br', 'distance_jp'),
		'BRKE' => array('distance_br', 'distance_ke'),
		'BRHU' => array('distance_br', 'distance_hu'),
		'BRMA' => array('distance_br', 'distance_ma'),
		'BRNG' => array('distance_br', 'distance_ng'),
		'BRRO' => array('distance_br', 'distance_ro'),
                'BRTR' => array('distance_br', 'distance_tr')
        );
	
        /**
        * Function to fetch the distance between two postal/zip codes (postal/zip code 1 vs postal/zip code 2).
        * The usage of this calculation works best within Canada & USA although it will also work for other countries.
        * Additionally this function will accept two city names (city name 1 vs city name 2).  This function is now cachable.
        *
        * @param       string       zip code 1
        * @param       string       zip code 2
        * @param       string       zip code search type (de, usde, cande, nlde, etc)
        * @param       string       city 1
        * @param       string       city 2
        * @param       string       force which country datastore to use (CAN, USA, CANUSA, USACAN, etc)
        *
        * @return      integer      This function returns the actual distance between the two elements and
        *                           will also properly format the result calculation (based on Miles)
        *                           into KM if you have this option enabled within the AdminCP.
        */
        function fetch_distance_response($zipcode1 = '', $zipcode2 = '', $zipcodetype = '', $city1 = '', $city2 = '', $inputdistance = 0)
        {
                global $ilconfig, $ilance;
                $distance = '';
                $response = ($inputdistance > 0) ? $inputdistance : $this->fetch_distance($zipcode1, $zipcode2, $zipcodetype);
                if ($response > 0)
                {
                        $distance = ($ilconfig['globalserver_distanceformula'] > 0)
				? round(($response * $ilconfig['globalserver_distanceformula']), 1) . ' ' . $ilconfig['globalserver_distanceresults']
				: round($response, 1) . ' ' . $ilconfig['globalserver_distanceresults'];
                }
                else if ($zipcode1 == $zipcode2)
                {
                        $distance = '&#177; 1 ' . $ilconfig['globalserver_distanceresults'];
                }
                else if ($response == 0)
                {
                        $distance = '-';
                }
                return $distance;
        }
        
        /**
        * Function to print the results of the internal function fetch_distance_response to fetch the
        * distance between two postal/zip codes (postal/zip code 1 vs postal/zip code 2).
        *
        * @param       integer      country id 1
        * @param       string       zip code 1
        * @param       string       country id 2 (optional)
        * @param       string       zip code 2
        * @param       integer      input distance (optional if we already have a number and just want formatting)
        *
        * @return      string       This function returns the actual distance between two areas (ie: 39.4 KM)
        *                           otherwise the printed result will be " - "
        */
        function print_distance_results($countryid1 = 0, $zipcode1 = '', $countryid2 = 0, $zipcode2 = '', $inputdistance = 0)
        {   
                global $ilance, $ilconfig, $ilpage, $phrase;
                if (in_array($countryid1, $this->accepted_countries) AND in_array($countryid2, $this->accepted_countries) AND !empty($zipcode1) AND !empty($zipcode2))
                {
                        // #### SAME COUNTRIES #################################
                        if ($countryid1 == $countryid2)
                        {
                                return $this->fetch_distance_response($zipcode1, $zipcode2, $this->countries["$countryid1"], '', '', $inputdistance);
                        }
                        // #### DIFFERENT COUNTRIES ############################
                        else
                        {
                                return $this->fetch_distance_response($zipcode1, $zipcode2, $this->countries["$countryid1"] . $this->countries["$countryid2"], '', '', $inputdistance);
                        }
                }
                return '-';
        }
        
        /**
        * Function to calculate the great circle distance between two zip or postal codes.
        *
        * @param       integer      latitude 1
        * @param       integer      longitude 1
        * @param       integer      latitude 2
        * @param       integer      longitude 2
        *
        * @return      integer      This function returns the distance between two longitude and latitude coordinates (default = miles)
        */
	function great_circle_distance($lat1 = '', $lon1 = '', $lat2 = '', $lon2 = '')
	{
                global $ilconfig;
		$theta = ($lon1 - $lon2);
		$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$final = ($dist * 60 * 1.1515);
		return $final;
	}
	
        /**
        * Function to calculate the great circle distance between two zip or postal codes.
        *
        * @param       string       zip code 1
        * @param       string       zip code 2
        * @param       string       zip code fetch type (de, usde, cande, nlde, etc)
        *
        * @return      integer      This function returns the distance between two longitude and latitude coordinates (default = miles)
        */
	function fetch_distance($zipcode1 = '', $zipcode2 = '', $zipcodetype = '')
	{
		global $ilance;
                $lat1 = $lon1 = $lat2 = $lon2 = 0;
		if (!empty($this->dbtables["$zipcodetype"]))
		{
			if (is_array($this->dbtables["$zipcodetype"]))
			{
				// zip code 1 and 2 from different countries
				$table1 = $this->dbtables["$zipcodetype"][0];
				$table2 = $this->dbtables["$zipcodetype"][1];
			}
			else
			{
				// zip code 1 and 2 from same country
				$table1 = $this->dbtables["$zipcodetype"];
				$table2 = $this->dbtables["$zipcodetype"];
			}
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "Latitude, Longitude
				FROM " . DB_PREFIX . "$table1
				WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode1))) . "'
                                LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$resl = $ilance->db->fetch_array($sql, DB_ASSOC);
				$lat1 = $resl['Latitude'];
				$lon1 = $resl['Longitude'];
			}
                        unset($resl, $sql);
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "Latitude, Longitude
				FROM " . DB_PREFIX . "$table2
				WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode2))) . "'
                                LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$resl = $ilance->db->fetch_array($sql, DB_ASSOC);
				$lat2 = $resl['Latitude'];
				$lon2 = $resl['Longitude'];
			}
                        unset($resl, $sql);
			return $this->great_circle_distance($lat1, $lon1, $lat2, $lon2);
		}
		return 0;
	}	
	
        /**
        * Function to fetch a single specific longitude and latitude point for a given (zip, postal code or city name).
        *
        * @param       string       zip or postal code
        * @param       integer      country id
        *
        * @return      integer      This function returns the longitude and latitude points for a given zip/postal/city name
        */
	function fetch_zip_longitude_latitude($zipcode = '', $countryid = '')
	{
		global $ilance;
		if (in_array($countryid, $this->accepted_countries))
		{
                        $sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "Latitude, Longitude
				FROM " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . "
				WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                return $ilance->db->fetch_array($sql, DB_ASSOC);
                        }
		}			
                return array();
	}
	
        /**
        * Function to fetch valid sql code based on distance calculation mainly used within the search system
        *
        * @param       string       zip code
        * @param       integer      country id
        * @param       string       field name of zipcode
        *
        * @return      array        Returns array holding 'leftjoin' and their associated distance 'fields'
        */
        function fetch_sql_as_distance($zipcode = '', $countryid = 0, $fieldname = '')
        {
                $details = $this->fetch_zip_longitude_latitude($zipcode, $countryid);
                if (empty($details) OR !is_array($details) OR count($details) == 0)
		{
                        $details['Latitude'] = $details['Longitude'] = 0;
		}
                if (in_array($countryid, $this->accepted_countries))
                {
                        $return['leftjoin'] = " LEFT JOIN " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . " z ON ($fieldname = z.ZIPCode) ";
                        $return['fields'] = ", (3958 * 3.1415926 * sqrt((z.Latitude - $details[Latitude]) * (z.Latitude - $details[Latitude]) + cos(z.Latitude / 57.29578) * cos($details[Latitude] / 57.29578) * (z.Longitude - $details[Longitude]) * (z.Longitude - $details[Longitude])) / 180) AS distance ";
                }
                if (!empty($return))
                {
                        return $return;
                }
                return false;
        }
        
        /**
        * Function to return an array of the zip or postal codes within $radius of $zipcode.
        * Returns an array with keys as the zip or postal codes and their cooresponding values as
        * the distance from the zipcode defined in $zipcode.
        *
        * @param       string       table name of requested zipcode data
        * @param       string       fieldname of zipcode
        * @param       string       zip or postal code
        * @param       integer      radius to search
        * @param       integer      country id
        * @param       boolean      include distance in the array output? ie: [90210] => 33.5 (default false)
        * @param       boolean      defines if the returned output is an SQL left join (to use later in search)
        * @param       boolean      defines if the returned output should only contain the city name for the zip code
        *
        * @return      integer      This function returns the longitude and latitude points for a given zip or postal code
        */
	function fetch_zips_in_range($zipcodetable = '', $fieldname = '', $zipcode = '', $radius = '', $countryid = '', $includedistance = false, $leftjoinonly = false, $radiusjoin = false, $fetchcityonly = false)
	{
		global $ilance;
		if ($fetchcityonly)
		{
			if (in_array($countryid, $this->accepted_countries))
                        {
				if ($ilance->db->field_exists('City', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]))
				{
					$sql = $ilance->db->query("
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "City
						FROM " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . "
						WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						return stripslashes($res['City']);
					}
				}
			}
			return false;
		}
		$details = $this->fetch_zip_longitude_latitude($zipcode, $countryid);
		if (count($details) == 0)
		{
                        $details['Latitude'] = $details['Longitude'] = 0;
		}
		$lat_range = ($radius / 69.172);
		$lon_range = abs($radius / (cos(deg2rad($details['Latitude'])) * 69.172));
		$min_lat = number_format($details['Latitude'] - $lat_range, '4', '.', '');
                $min_lon = number_format($details['Longitude'] - $lon_range, '4', '.', '');                
		$max_lat = number_format($details['Latitude'] + $lat_range, '4', '.', '');
		$max_lon = number_format($details['Longitude'] + $lon_range, '4', '.', '');
		$return = array();
                $return['condition'] = '';
		// #### prepare an sql statement for include in our advanced search
                if ($leftjoinonly)
                {
                        if (in_array($countryid, $this->accepted_countries))
                        {
                                $return['leftjoin'] = " LEFT JOIN " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . " z ON ($fieldname = z.ZIPCode) ";
                                $return['fields'] = ", (3958 * 3.1415926 * sqrt((z.Latitude - $details[Latitude]) * (z.Latitude - $details[Latitude]) + cos(z.Latitude / 57.29578) * cos($details[Latitude] / 57.29578) * (z.Longitude - $details[Longitude]) * (z.Longitude - $details[Longitude])) / 180) AS distance ";
                                $return['condition'] = "AND (z.Latitude BETWEEN '$min_lat' AND '$max_lat') AND (z.Longitude BETWEEN '$min_lon' AND '$max_lon') ";
                        }
                }
		// #### ask the database for the zip, long and lat surrounding supplied zip code
                else
                {
                        if (in_array($countryid, $this->accepted_countries))
                        {
                                $sql = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "c.ZIPCode, c.Latitude, c.Longitude
                                        FROM " . DB_PREFIX . "$zipcodetable
                                        LEFT JOIN " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . " c ON ($fieldname = c.ZIPCode)
                                        WHERE (c.Latitude BETWEEN '$min_lat' AND '$max_lat') AND (c.Longitude BETWEEN '$min_lon' AND '$max_lon')
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                        {
                                                $dist = $this->great_circle_distance($details['Latitude'], $details['Longitude'], $res['Latitude'], $res['Longitude']);
                                                if ($includedistance)
                                                {
                                                        if ($dist <= $radius)
                                                        {
                                                                $return[$res['ZIPCode']] = round($dist, 2);
                                                        }
                                                }
                                                else
                                                {
                                                        if ($dist <= $radius)
                                                        {
                                                                $return[] = $res['ZIPCode'];
                                                        }
                                                }
                                        }
                                        if ($radiusjoin)
                                        {
                                                if (isset($return) AND count($return) > 0)
                                                {
							$return = array_unique($return);
                                                        $vmp = 'AND (';
							$vmpx = '';
                                                        foreach ($return AS $zipcode)
                                                        {
                                                                if (!empty($zipcode))
                                                                {
                                                                        $vmpx .= " $fieldname LIKE '%" . $ilance->db->escape_string(format_zipcode($zipcode)) . "%' OR";
                                                                }
                                                        }
							if (!empty($vmpx))
							{
								$tmp = $vmp . $vmpx;
								$tmp = mb_substr($tmp, 0, -3);
								$return['condition'] .= $tmp . ')';
							}
                                                }
                                        }
                                }        
                        }
                }
		return $return;
	}
	
	function fetch_installed_countries()
	{
		global $ilance, $show;
		$rows_res = array();
		$show['nodistancerows'] = true;
		$rows = array();
		foreach ($this->dbtables AS $shortlng => $dbtable)
		{
			if (!is_array($dbtable) AND $ilance->db->table_exists(DB_PREFIX . $dbtable))
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "ZIPCode
					FROM " . DB_PREFIX . $dbtable . "
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$show['nodistancerows'] = false;
					$rows['title'] = $this->distance_titles[$shortlng];
					$rows['cities'] = $this->distance_cities[$shortlng];
					$rows['states'] = $this->distance_states[$shortlng];
					$rows['zipcodecount'] = $this->distance_count[$shortlng];
					$rows_res[] = $rows;
				}
			}
		}
		return $rows_res;
	}
	function fetch_state_from_zipcode($countryid = 0, $zipcode = '')
	{
		global $ilance;
		if (in_array($countryid, $this->accepted_countries))
		{
			if ($ilance->db->field_exists('State', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]))
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "State
					FROM " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . "
					WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					return stripslashes($res['State']);
				}
			}
			else if ($ilance->db->field_exists('Province', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]))
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "Province
					FROM " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . "
					WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					return stripslashes($res['Province']);
				}
			}
		}
		return false;
	}
	function fetch_city_from_zipcode($countryid = 0, $state = '', $zipcode = '')
	{
		global $ilance;
		if (in_array($countryid, $this->accepted_countries))
		{
			if ($ilance->db->field_exists('City', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]) AND $ilance->db->field_exists('State', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]))
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "City
					FROM " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . "
					WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'
						AND State = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($state))) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					return stripslashes($res['City']);
				}
			}
			else if ($ilance->db->field_exists('City', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]) AND $ilance->db->field_exists('Province', DB_PREFIX . $this->dbtables[$this->countries["$countryid"]]))
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "City
					FROM " . DB_PREFIX . $this->dbtables[$this->countries["$countryid"]] . "
					WHERE ZIPCode = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($zipcode))) . "'
						AND Province = '" . $ilance->db->escape_string(str_replace(' ', '', strtoupper($state))) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					return stripslashes($res['City']);
				}
			}
		}
		return false;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>