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
* Common class which holds the majority of common ILance functions in the system
*
* @package      iLance\Common
* @version      4.0.0.8059
* @author       ILance
*/
class common
{
	/**
        * Constructor
        */
	function common()
	{
	}
	
        /**
	* Function to determine what the visiting users web browser is
	*
	* @param       string        browser
	* @param       integer       version (optional)
	* 
        * @return      string        Returns browser info
	*/
	function is_webbrowser($browser, $version = 0)
	{
		global $_SERVER;
		static $is;
                
                $agent = mb_strtolower(USERAGENT);
                
		if (!is_array($is))
		{
			$useragent = $agent;
			$is = array(
                                'opera' => 0,
                                'ie' => 0,
                                'mozilla' => 0,
                                'firebird' => 0,
                                'firefox' => 0,
                                'camino' => 0,
                                'konqueror' => 0,
                                'safari' => 0,
                                'webtv' => 0,
                                'netscape' => 0,
                                'mac' => 0,
                                'chrome' => 0,
                                'aol' => 0,
                                'lynx' => 0,
                                'phoenix' => 0,
                                'omniweb' => 0,
                                'icab' => 0,
                                'mspie' => 0,
                                'netpositive' => 0,
                                'galeon' => 0,
				'iphone' => 0,
				'ipad' => 0,
				'android' => 0,
				'blackberry' => 0,
                        );
                        
			if (mb_strpos($useragent, 'opera') !== false)
			{
				preg_match('#opera(/| )([0-9\.]+)#', $useragent, $regs);
				$is['opera'] = $regs[2];
			}
			if (mb_strpos($useragent, 'msie ') !== false AND !$is['opera'])
			{
				preg_match('#msie ([0-9\.]+)#', $useragent, $regs);
				$is['ie'] = $regs[1];
			}
			if (mb_strpos($useragent, 'mac') !== false)
			{
				$is['mac'] = 1;
			}
			if (mb_strpos($useragent, 'camino') !== false)
			{
				$is['camino'] = 1;
			}
                        if (mb_strpos($useragent, 'chrome') !== false)
			{
				$is['chrome'] = 1;
			}
			if (mb_strpos($useragent, 'safari') !== false OR mb_strpos($useragent, 'safari') !== false AND $is['mac'])
			{
				preg_match('#safari/([0-9\.]+)#', $useragent, $regs);
				$is['safari'] = $regs[1];
			}
			if (mb_strpos($useragent, 'konqueror') !== false)
			{
				preg_match('#konqueror/([0-9\.-]+)#', $useragent, $regs);
				$is['konqueror'] = $regs[1];
			}
			if (mb_strpos($useragent, 'gecko') !== false AND !$is['safari'] AND !$is['konqueror'] AND !$is['chrome'])
			{
				preg_match('#gecko/(\d+)#', $useragent, $regs);
				$is['mozilla'] = $regs[1];
				if (mb_strpos($useragent, 'firefox') !== false OR mb_strpos($useragent, 'firebird') !== false OR mb_strpos($useragent, 'phoenix') !== false)
				{
					preg_match('#(phoenix|firebird|firefox)( browser)?/([0-9\.]+)#', $useragent, $regs);
					$is['firebird'] = $regs[3];
					if ($regs[1] == 'firefox')
					{
						$is['firefox'] = $regs[3];
					}
				}
				if (mb_strpos($useragent, 'chimera') !== false OR mb_strpos($useragent, 'camino') !== false)
				{
					preg_match('#(chimera|camino)/([0-9\.]+)#', $useragent, $regs);
					$is['camino'] = $regs[2];
				}
			}
			if (mb_strpos($useragent, 'webtv') !== false)
			{
				preg_match('#webtv/([0-9\.]+)#', $useragent, $regs);
				$is['webtv'] = $regs[1];
			}
			if (preg_match('#mozilla/([1-4]{1})\.([0-9]{2}|[1-8]{1})#', $useragent, $regs))
			{
				$is['netscape'] = "$regs[1].$regs[2]";
			}
                        if (mb_strpos($useragent, 'aol') !== false)
			{
				$is['aol'] = 1;
			}
                        if (mb_strpos($useragent, 'lynx') !== false)
			{
				$is['lynx'] = 1;
			}
                        if (mb_strpos($useragent, 'phoenix') !== false)
			{
				$is['phoenix'] = 1;
			}
			if (mb_strpos($useragent, 'firebird') !== false)
			{
				$is['firebird'] = 1;
			}
                        if (mb_strpos($useragent, 'omniweb') !== false)
			{
				$is['omniweb'] = 1;
			}
                        if (mb_strpos($useragent, 'icab') !== false)
			{
				$is['icab'] = 1;
			}
                        if (mb_strpos($useragent, 'mspie') !== false)
			{
				$is['mspie'] = 1;
			}
                        if (mb_strpos($useragent, 'netpositive') !== false)
			{
				$is['netpositive'] = 1;
			}
                        if (mb_strpos($useragent, 'galeon') !== false)
			{
				$is['galeon'] = 1;
			}
			if (mb_strpos($useragent, 'iphone') !== false)
			{
				$is['iphone'] = 1;
			}
			if (mb_strpos($useragent, 'ipad') !== false)
			{
				$is['ipad'] = 1;
			}
			if (mb_strpos($useragent, 'android') !== false)
			{
				$is['android'] = 1;
			}
			if (mb_strpos($useragent, 'blackberry') !== false)
			{
				$is['blackberry'] = 1;
			}
		}
                
		$browser = mb_strtolower($browser);
		if (mb_substr($browser, 0, 3) == 'is_')
		{
			$browser = mb_substr($browser, 3);
		}
                
		if ($is["$browser"])
		{
			if ($version)
			{
				if ($is["$browser"] >= $version)
				{
					return $is["$browser"];
				}
			}
			else
			{
				return $is["$browser"];
			}
		}
                
		return 0;
	}
	
        /**
	* Function to return a utf-8 string based on a numeric entity string
	*
	* @param       string        numeric entity character eg: &320; 
	* 
        * @return      string        Returns utf-8 character based on numeric entities supplied
	*/
	function numeric_to_utf8($t = '')
	{
		$convmap = array(0x0, 0x2FFFF, 0, 0xFFFF);
                
		return mb_decode_numericentity($t, $convmap, 'UTF-8');
	}
    
	/**
	* Function to return numeric entities from htmlentity characters
	* 
	* @param string
	*
	* @return      string
	*/
	function entities_to_numeric($text = '', $flip = 0, $skip = '')
	{
		$to_ncr = array(
                        'Âˇ'         => '&#161;',
                        'Â˘'         => '&#162;',
                        'ÂŁ'         => '&#163;',
                        'Â¤'         => '&#164;',
                        'ÂĄ'         => '&#165;',
                        'Â¦'         => '&#166;',
                        'Â§'         => '&#167;',
                        'Â¨'         => '&#168;',
                        'Â©'         => '&#169;',
                        'ÂŞ'         => '&#170;',
                        'Â«'         => '&#171;',
                        'Â¬'         => '&#172;',
                        'Â®'         => '&#174;',
                        'ÂŻ'         => '&#175;',
                        'Â°'         => '&#176;',
                        'Â±'         => '&#177;',
                        'Â˛'         => '&#178;',
                        'Âł'         => '&#179;',
                        'Â´'         => '&#180;',
                        'Âµ'         => '&#181;',
                        'Â¶'         => '&#182;',
                        'Â·'         => '&#183;',
                        'Â¸'         => '&#184;',
                        'Âą'         => '&#185;',
                        'Âş'         => '&#186;',
                        'Â»'         => '&#187;',
                        'ÂĽ'         => '&#188;',
                        'Â˝'         => '&#189;',
                        'Âľ'         => '&#190;',
                        'Âż'         => '&#191;',
                        'Ă€'         => '&#192;',
                        'Ă?'         => '&#193;',
                        'Ă‚'         => '&#194;',
                        'Ă?'         => '&#195;',
                        'Ă„'         => '&#196;',
                        'Ă…'         => '&#197;',
                        'Ă†'         => '&#198;',
                        'Ă‡'         => '&#199;',
                        'Ă?'         => '&#200;',
                        'Ă‰'         => '&#201;',
                        'ĂŠ'         => '&#202;',
                        'Ă‹'         => '&#203;',
                        'ĂŚ'         => '&#204;',
                        'ĂŤ'         => '&#205;',
                        'ĂŽ'         => '&#206;',
                        'ĂŹ'         => '&#207;',
                        'Ă?'         => '&#208;',
                        'Ă‘'         => '&#209;',
                        'Ă’'         => '&#210;',
                        'Ă“'         => '&#211;',
                        'Ă”'         => '&#212;',
                        'Ă•'         => '&#213;',
                        'Ă–'         => '&#214;',
                        'Ă—'         => '&#215;',
                        'Ă?'         => '&#216;',
                        'Ă™'         => '&#217;',
                        'Ăš'         => '&#218;',
                        'Ă›'         => '&#219;',
                        'Ăś'         => '&#220;',
                        'Ăť'         => '&#221;',
                        'Ăž'         => '&#222;',
                        'Ăź'         => '&#223;',
                        'Ă '         => '&#224;',
                        'Ăˇ'         => '&#225;',
                        'Ă˘'         => '&#226;',
                        'ĂŁ'         => '&#227;',
                        'Ă¤'         => '&#228;',
                        'ĂĄ'         => '&#229;',
                        'Ă¦'         => '&#230;',
                        'Ă§'         => '&#231;',
                        'Ă¨'         => '&#232;',
                        'Ă©'         => '&#233;',
                        'ĂŞ'         => '&#234;',
                        'Ă«'         => '&#235;',
                        'Ă¬'         => '&#236;',
                        'Ă­'         => '&#237;',
                        'Ă®'         => '&#238;',
                        'ĂŻ'         => '&#239;',
                        'Ă°'         => '&#240;',
                        'Ă±'         => '&#241;',
                        'Ă˛'         => '&#242;',
                        'Ăł'         => '&#243;',
                        'Ă´'         => '&#244;',
                        'Ăµ'         => '&#245;',
                        'Ă¶'         => '&#246;',
                        'Ă·'         => '&#247;',
                        'Ă¸'         => '&#248;',
                        'Ăą'         => '&#249;',
                        'Ăş'         => '&#250;',
                        'Ă»'         => '&#251;',
                        'ĂĽ'         => '&#252;',
                        'Ă˝'         => '&#253;',
                        'Ăľ'         => '&#254;',
                        'Ăż'         => '&#255;',
                        '&quot;'    => '&#34;',
                        '&amp;'     => '&#38;',
                        '&frasl;'   => '&#47;',
                        '&lt;'      => '&#60;',
                        '&gt;'      => '&#62;',
                        '|'         => '&#124;',
                        '&nbsp;'    => '&#160;',
                        '&iexcl;'   => '&#161;',
                        '&cent;'    => '&#162;',
                        '&pound;'   => '&#163;',
                        '&curren;'  => '&#164;',
                        '&yen;'     => '&#165;',
                        '&brvbar;'  => '&#166;',
                        '&brkbar;'  => '&#166;',
                        '&sect;'    => '&#167;',
                        '&uml;'     => '&#168;',
                        '&die;'     => '&#168;',
                        '&copy;'    => '&#169;',
                        '&ordf;'    => '&#170;',
                        '&laquo;'   => '&#171;',
                        '&not;'     => '&#172;',
                        '&shy;'     => '&#173;',
                        '&reg;'     => '&#174;',
                        '&macr;'    => '&#175;',
                        '&hibar;'   => '&#175;',
                        '&deg;'     => '&#176;',
                        '&plusmn;'  => '&#177;',
                        '&sup2;'    => '&#178;',
                        '&sup3;'    => '&#179;',
                        '&acute;'   => '&#180;',
                        '&micro;'   => '&#181;',
                        '&para;'    => '&#182;',
                        '&middot;'  => '&#183;',
                        '&cedil;'   => '&#184;',
                        '&sup1;'    => '&#185;',
                        '&ordm;'    => '&#186;',
                        '&raquo;'   => '&#187;',
                        '&frac14;'  => '&#188;',
                        '&frac12;'  => '&#189;',
                        '&frac34;'  => '&#190;',
                        '&iquest;'  => '&#191;',
                        '&Agrave;'  => '&#192;',
                        '&Aacute;'  => '&#193;',
                        '&Acirc;'   => '&#194;',
                        '&Atilde;'  => '&#195;',
                        '&Auml;'    => '&#196;',
                        '&Aring;'   => '&#197;',
                        '&AElig;'   => '&#198;',
                        '&Ccedil;'  => '&#199;',
                        '&Egrave;'  => '&#200;',
                        '&Eacute;'  => '&#201;',
                        '&Ecirc;'   => '&#202;',
                        '&Euml;'    => '&#203;',
                        '&Igrave;'  => '&#204;',
                        '&Iacute;'  => '&#205;',
                        '&Icirc;'   => '&#206;',
                        '&Iuml;'    => '&#207;',
                        '&ETH;'     => '&#208;',
                        '&Ntilde;'  => '&#209;',
                        '&Ograve;'  => '&#210;',
                        '&Oacute;'  => '&#211;',
                        '&Ocirc;'   => '&#212;',
                        '&Otilde;'  => '&#213;',
                        '&Ouml;'    => '&#214;',
                        '&times;'   => '&#215;',
                        '&Oslash;'  => '&#216;',
                        '&Ugrave;'  => '&#217;',
                        '&Uacute;'  => '&#218;',
                        '&Ucirc;'   => '&#219;',
                        '&Uuml;'    => '&#220;',
                        '&Yacute;'  => '&#221;',
                        '&THORN;'   => '&#222;',
                        '&szlig;'   => '&#223;',
                        '&agrave;'  => '&#224;',
                        '&aacute;'  => '&#225;',
                        '&acirc;'   => '&#226;',
                        '&atilde;'  => '&#227;',
                        '&auml;'    => '&#228;',
                        '&aring;'   => '&#229;',
                        '&aelig;'   => '&#230;',
                        '&ccedil;'  => '&#231;',
                        '&egrave;'  => '&#232;',
                        '&eacute;'  => '&#233;',
                        '&ecirc;'   => '&#234;',
                        '&euml;'    => '&#235;',
                        '&igrave;'  => '&#236;',
                        '&iacute;'  => '&#237;',
                        '&icirc;'   => '&#238;',
                        '&iuml;'    => '&#239;',
                        '&eth;'     => '&#240;',
                        '&ntilde;'  => '&#241;',
                        '&ograve;'  => '&#242;',
                        '&oacute;'  => '&#243;',
                        '&ocirc;'   => '&#244;',
                        '&otilde;'  => '&#245;',
                        '&ouml;'    => '&#246;',
                        '&divide;'  => '&#247;',
                        '&oslash;'  => '&#248;',
                        '&ugrave;'  => '&#249;',
                        '&uacute;'  => '&#250;',
                        '&ucirc;'   => '&#251;',
                        '&uuml;'    => '&#252;',
                        '&yacute;'  => '&#253;',
                        '&thorn;'   => '&#254;',
                        '&yuml;'    => '&#255;',
                        '&OElig;'   => '&#338;',
                        '&oelig;'   => '&#339;',
                        '&Scaron;'  => '&#352;',
                        '&scaron;'  => '&#353;',
                        '&Yuml;'    => '&#376;',
                        '&fnof;'    => '&#402;',
                        '&circ;'    => '&#710;',
                        '&tilde;'   => '&#732;',
                        '&Alpha;'   => '&#913;',
                        '&Beta;'    => '&#914;',
                        '&Gamma;'   => '&#915;',
                        '&Delta;'   => '&#916;',
                        '&Epsilon;' => '&#917;',
                        '&Zeta;'    => '&#918;',
                        '&Eta;'     => '&#919;',
                        '&Theta;'   => '&#920;',
                        '&Iota;'    => '&#921;',
                        '&Kappa;'   => '&#922;',
                        '&Lambda;'  => '&#923;',
                        '&Mu;'      => '&#924;',
                        '&Nu;'      => '&#925;',
                        '&Xi;'      => '&#926;',
                        '&Omicron;' => '&#927;',
                        '&Pi;'      => '&#928;',
                        '&Rho;'     => '&#929;',
                        '&Sigma;'   => '&#931;',
                        '&Tau;'     => '&#932;',
                        '&Upsilon;' => '&#933;',
                        '&Phi;'     => '&#934;',
                        '&Chi;'     => '&#935;',
                        '&Psi;'     => '&#936;',
                        '&Omega;'   => '&#937;',
                        '&alpha;'   => '&#945;',
                        '&beta;'    => '&#946;',
                        '&gamma;'   => '&#947;',
                        '&delta;'   => '&#948;',
                        '&epsilon;' => '&#949;',
                        '&zeta;'    => '&#950;',
                        '&eta;'     => '&#951;',
                        '&theta;'   => '&#952;',
                        '&iota;'    => '&#953;',
                        '&kappa;'   => '&#954;',
                        '&lambda;'  => '&#955;',
                        '&mu;'      => '&#956;',
                        '&nu;'      => '&#957;',
                        '&xi;'      => '&#958;',
                        '&omicron;' => '&#959;',
                        '&pi;'      => '&#960;',
                        '&rho;'     => '&#961;',
                        '&sigmaf;'  => '&#962;',
                        '&sigma;'   => '&#963;',
                        '&tau;'     => '&#964;',
                        '&upsilon;' => '&#965;',
                        '&phi;'     => '&#966;',
                        '&chi;'     => '&#967;',
                        '&psi;'     => '&#968;',
                        '&omega;'   => '&#969;',
                        '&thetasym;'=> '&#977;',
                        '&upsih;'   => '&#978;',
                        '&piv;'     => '&#982;',
                        '&ensp;'    => '&#8194;',
                        '&emsp;'    => '&#8195;',
                        '&thinsp;'  => '&#8201;',
                        '&zwnj;'    => '&#8204;',
                        '&zwj;'     => '&#8205;',
                        '&lrm;'     => '&#8206;',
                        '&rlm;'     => '&#8207;',
                        '&ndash;'   => '&#8211;',
                        '&mdash;'   => '&#8212;',
                        '&lsquo;'   => '&#8216;',
                        '&rsquo;'   => '&#8217;',
                        '&sbquo;'   => '&#8218;',
                        '&ldquo;'   => '&#8220;',
                        '&rdquo;'   => '&#8221;',
                        '&bdquo;'   => '&#8222;',
                        '&dagger;'  => '&#8224;',
                        '&Dagger;'  => '&#8225;',
                        '&bull;'    => '&#8226;',
                        '&hellip;'  => '&#8230;',
                        '&permil;'  => '&#8240;',
                        '&prime;'   => '&#8242;',
                        '&Prime;'   => '&#8243;',
                        '&lsaquo;'  => '&#8249;',
                        '&rsaquo;'  => '&#8250;',
                        '&oline;'   => '&#8254;',
                        '&frasl;'   => '&#8260;',
                        '&euro;'    => '&#8364;',
                        '&image;'   => '&#8465;',
                        '&weierp;'  => '&#8472;',
                        '&real;'    => '&#8476;',
                        '&trade;'   => '&#8482;',
                        '&alefsym;' => '&#8501;',
                        '&larr;'    => '&#8592;',
                        '&uarr;'    => '&#8593;',
                        '&rarr;'    => '&#8594;',
                        '&darr;'    => '&#8595;',
                        '&harr;'    => '&#8596;',
                        '&crarr;'   => '&#8629;',
                        '&lArr;'    => '&#8656;',
                        '&uArr;'    => '&#8657;',
                        '&rArr;'    => '&#8658;',
                        '&dArr;'    => '&#8659;',
                        '&hArr;'    => '&#8660;',
                        '&forall;'  => '&#8704;',
                        '&part;'    => '&#8706;',
                        '&exist;'   => '&#8707;',
                        '&empty;'   => '&#8709;',
                        '&nabla;'   => '&#8711;',
                        '&isin;'    => '&#8712;',
                        '&notin;'   => '&#8713;',
                        '&ni;'      => '&#8715;',
                        '&prod;'    => '&#8719;',
                        '&sum;'     => '&#8721;',
                        '&minus;'   => '&#8722;',
                        '&lowast;'  => '&#8727;',
                        '&radic;'   => '&#8730;',
                        '&prop;'    => '&#8733;',
                        '&infin;'   => '&#8734;',
                        '&ang;'     => '&#8736;',
                        '&and;'     => '&#8743;',
                        '&or;'      => '&#8744;',
                        '&cap;'     => '&#8745;',
                        '&cup;'     => '&#8746;',
                        '&int;'     => '&#8747;',
                        '&there4;'  => '&#8756;',
                        '&sim;'     => '&#8764;',
                        '&cong;'    => '&#8773;',
                        '&asymp;'   => '&#8776;',
                        '&ne;'      => '&#8800;',
                        '&equiv;'   => '&#8801;',
                        '&le;'      => '&#8804;',
                        '&ge;'      => '&#8805;',
                        '&sub;'     => '&#8834;',
                        '&sup;'     => '&#8835;',
                        '&nsub;'    => '&#8836;',
                        '&sube;'    => '&#8838;',
                        '&supe;'    => '&#8839;',
                        '&oplus;'   => '&#8853;',
                        '&otimes;'  => '&#8855;',
                        '&perp;'    => '&#8869;',
                        '&sdot;'    => '&#8901;',
                        '&lceil;'   => '&#8968;',
                        '&rceil;'   => '&#8969;',
                        '&lfloor;'  => '&#8970;',
                        '&rfloor;'  => '&#8971;',
                        '&lang;'    => '&#9001;',
                        '&rang;'    => '&#9002;',
                        '&loz;'     => '&#9674;',
                        '&spades;'  => '&#9824;',
                        '&clubs;'   => '&#9827;',
                        '&hearts;'  => '&#9829;',
                        '&diams;'   => '&#9830;'
                );
    
                if (isset($flip) AND $flip)
                {
                        $to_ncr = array_flip($to_ncr);
                }
        
                foreach ($to_ncr AS $entity => $ncr)
                {
                        if (isset($skip) AND $skip != '')
                        {
                                if ($skip != $entity)
                                {
                                        $text = str_replace($entity, $ncr, $text);
                                }
                        }
                        else
                        {
                                $text = str_replace($entity, $ncr, $text);
                        }
                        
                }
                
                return $text;
	}        

	/**
	* Function to 
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function xhtml_entities_to_numeric_entities($text = '')
        {
                if (preg_match('~&#x([0-9a-fA-F]+);~', $text, $matches))
                {
                        //echo 'Found: &#x' . $matches[1] . '; (' . chr(hexdec('0x' . $matches[1])) . '). Replaced: &#' . hexdec('0x' . $matches[1]) . ';' . "\n";
                        $text = str_replace('&#x' . $matches[1] . ';', '&#' . hexdec('0x' . $matches[1]) . ';', $text);
                        $text = str_replace(array('&#62;', '&#60;'), array('&gt;', '&lt;'), $text);
                }
                
                return $text;
        }
        
        /**
	* Function to 
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function js_escaped_to_xhtml_entities($text = '')
        {
                $text = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($text));
                return $text;
        }
        
        /**
	* Function to strips invalid html such as javascript code
	* 
	* @param       string        text
	*
	* @return      string
	*/
	function xss_clean(&$var)
	{
		static
			$find = array('#javascript#i', '#ilancescript#i'),
			$replace = array('java script', 'ilance script');

		$var = preg_replace($find, $replace, htmlspecialchars_uni($var));
		return $var;
	}
	
        /**
	* Function to display the login information bar for members
	* 
	* @param       string        text
	*
	* @return      string
	*/
	function login_include()
	{
		global $phrase, $ilance, $ilpage, $ilconfig, $show;
                
		$hour = date('H');
		$ampm = date('A');
		$greeting = '{_hi}, ';
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND !empty($_SESSION['ilancedata']['user']['username']) AND !empty($_SESSION['ilancedata']['user']['password']))
                {
                        $login_include = ($ilconfig['globalauctionsettings_seourls']) ? $greeting . ' ' . $_SESSION['ilancedata']['user']['username'] . ' ~ <span class="blue"><a href="' . HTTPS_SERVER . 'signout?nc=' . time() . '" target="_self" onclick="return log_out();">{_log_out}</a></span>' : $greeting . ' ' . $_SESSION['ilancedata']['user']['username'] . ' ~ <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?cmd=_logout&amp;nc=' . time() . '" target="_self" onclick="return log_out();">{_log_out}</a></span>';
                }
                else
                {
                        if (!empty($_COOKIE[COOKIE_PREFIX . 'username']))
                        {
                                $login_include = ($ilconfig['globalauctionsettings_seourls']) ? $greeting . ' ' . $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']) . ' ~ <span class="blue"><a href="' . HTTPS_SERVER . 'signin?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '" target="_self">{_not_you}?</a></span>' : $greeting . ' ' . $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']) . ' ~ <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '" target="_self">{_not_you}?</a></span>';
                        }
                        else
                        {
                                $login_include = ($ilconfig['globalauctionsettings_seourls']) ? '{_welcome}! <span class="blue"><a href="' . HTTPS_SERVER . 'register">{_register}</a></span> {_or} <span class="blue"><a href="' . HTTPS_SERVER . 'signin?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '">{_sign_in}</a></span>' : '{_welcome}! <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['registration'] . '">{_register}</a></span> {_or} <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(strip_tags(SCRIPT_URI)) . '">{_sign_in}</a></span>';
                        }
                }
                
                ($apihook = $ilance->api('login_include_end')) ? eval($apihook) : false;
                
		if (!empty($login_include))
		{
			return $login_include;
		}
                return false;
                
	}
    
	/**
	* Function to display a date when a supplied user id was last seen
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function last_seen($userid, $location = false)
	{
		global $ilance, $ilconfig, $phrase;
                
		$sql = $ilance->db->query("
                        SELECT lastseen FROM " . DB_PREFIX . "users
                        WHERE user_id = '".intval($userid)."'
                ", 0, null, __FILE__, __LINE__);	
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			if ($res['lastseen'] == "0000-00-00 00:00:00")
			{
				$lastseen = '{_more_than_a_month_ago}';
			}
			else
			{
				$lastseen = print_date($res['lastseen'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			}
		}
                
		return $lastseen;
	}
	
	/**
	* Function to determine if an email address is valid.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function is_email_valid($email = '')
	{
		return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s]+\.+[a-z]{2,6}))$#si', $email);
	}
	
	/**
	* Function to determine if an email address is banned.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function is_email_banned($email = '')
	{
		global $ilconfig;
		if (!empty($ilconfig['registrationdisplay_emailban']))
		{
			$bans = preg_split('/\s+/', $ilconfig['registrationdisplay_emailban'], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($bans AS $banned)
			{
				if ($this->is_email_valid($banned))
				{
					$regex = '^' . preg_quote($banned, '#') . '$';
				}
				else
				{
					$regex = preg_quote($banned, '#');
				}
				if (preg_match("#$regex#i", $email))
				{
					return 1;
				}
			}
		}
		return 0;
	}
	
	/**
	* Function to determine if a username is banned.
	* 
	* @param       string        text
	*
	* @return      string
	*/
	function is_username_banned($username = '')
	{
		global $ilconfig, $ilance;
		$isbanned = false;
		if (!empty($ilconfig['registrationdisplay_userban']))
		{
			$bans = preg_split('/\s+/', $ilconfig['registrationdisplay_userban'], -1, PREG_SPLIT_NO_EMPTY);
			foreach ($bans AS $banned)
			{
				$regex = '^' . preg_quote($banned, '#') . '$';
				if (preg_match("#$regex#i", $username))
				{
					$isbanned = true;
				}
			}
		}		
		$pregextra = '';
		
		($apihook = $ilance->api('is_username_banned_end')) ? eval($apihook) : false;
		
		if ($ilconfig['registration_allow_special'] == 0)
		{
			if (preg_match('/[^a-zA-Z0-9' . $pregextra . '\_.]+/', $username))
			{ // check if anything other than a to z, A to Z and 0 to 9 _ and .
				$isbanned = true;
			}
			if (preg_match("/\\s/", $username))
			{ // check if user is using spaces
				$isbanned = true;
			}
		}
		return $isbanned;
	}
	
	/**
	* Function to download a file to a web browser.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function download_file($filestring = '', $filename = '', $filetype = '')
	{
		if (!isset($isIE))
		{
			static $isIE;
			$isIE = iif($this->is_webbrowser('ie') OR $this->is_webbrowser('opera'), true, false);
		}
		if ($isIE)
		{
			$filetype = 'application/octetstream';
		}
		else
		{
			$filetype = 'application/octet-stream';
		}
		header('Content-Type: ' . $filetype);
		header('Expires: ' . date('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		//header('Content-Length: ' . strlen($filestring));
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
                
		echo $filestring;
		exit();
	}
	
	/**
	* Function to generate a random string based on a supplied number of characters.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function construct_random_value($num)
	{
		switch($num)
		{
			case "1":
			$rand = "A";
			break;
			case "2":
			$rand = "B";
			break;
			case "3":
			$rand = "C";
			break;
			case "4":
			$rand = "D";
			break;
			case "5":
			$rand = "E";
			break;
			case "6":
			$rand = "F";
			break;
			case "7":
			$rand = "G";
			break;
			case "8":
			$rand = "H";
			break;
			case "9":
			$rand = "I";
			break;
			case "10":
			$rand = "J";
			break;
			case "11":
			$rand = "K";
			break;
			case "12":
			$rand = "L";
			break;
			case "13":
			$rand = "M";
			break;
			case "14":
			$rand = "N";
			break;
			case "15":
			$rand = "O";
			break;
			case "16":
			$rand = "P";
			break;
			case "17":
			$rand = "Q";
			break;
			case "18":
			$rand = "R";
			break;
			case "19":
			$rand = "S";
			break;
			case "20":
			$rand = "T";
			break;
			case "21":
			$rand = "U";
			break;
			case "22":
			$rand = "V";
			break;
			case "23":
			$rand = "W";
			break;
			case "24":
			$rand = "X";
			break;
			case "25":
			$rand = "Y";
			break;
			case "26":
			$rand = "Z";
			break;
			case "27":
			$rand = "0";
			break;
			case "28":
			$rand = "1";
			break;
			case "29":
			$rand = "2";
			break;
			case "30":
			$rand = "3";
			break;
			case "31":
			$rand = "4";
			break;
			case "32":
			$rand = "5";
			break;
			case "33":
			$rand = "6";
			break;
			case "34":
			$rand = "7";
			break;
			case "35":
			$rand = "8";
			break;
			case "36":
			$rand = "9";
			break;
		}
                
		return $rand;
	}
	
	/**
	* Function to fetch the active web browser name.
	* 
	* @param       string        text
	*
	* @return      string
	*/
        function fetch_browser_name($showicon = 0, $readname = '')
	{
		global $ilance, $ilconfig, $phrase;
                
		if (isset($readname) AND $readname != '')
		{
			if ($readname == 'ie')
			{
				$name = 'Internet Explorer';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ie.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'opera')
			{
				$name = 'Opera';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/opera.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'firefox' OR $readname == 'firebird' OR $readname == 'phoenix')
			{
				$name = 'FireFox';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/firefox.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'camino')
			{
				$name = 'Camino';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'].$ilconfig['template_imagesfolder'] . 'icons/camino.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'konqueror')
			{
				$name = 'Konqueror';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/konqueror.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'chrome')
			{
				$name = 'Chrome';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/chrome.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'safari')
			{
				$name = 'Safari';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/safari.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'netscape')
			{
				$name = 'Netscape';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/netscape.gif" border="0" alt="' . $name . '" />';
			}
                        else if ($readname == 'webtv')
			{
				$name = 'WebTV';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/webtv.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'lynx')
			{
				$name = 'Lynx';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/lynx.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'omniweb')
			{
				$name = 'Omniweb';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/omniweb.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'icab')
			{
				$name = 'iCab';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/icab.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'mspie')
			{
				$name = 'mspie';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mspie.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'netpositive')
			{
				$name = 'NetPositive';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/netpositive.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'galeon')
			{
				$name = 'Galeon';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/galeon.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'iphone')
			{
				$name = 'iPhone';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/iphone.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'ipad')
			{
				$name = 'iPad';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ipad.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'blackberry')
			{
				$name = 'BlackBerry';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blackberry.gif" border="0" alt="' . $name . '" />';
			}
			else if ($readname == 'android')
			{
				$name = 'Android';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/android.gif" border="0" alt="' . $name . '" />';
			}
			else
			{
				$name = '{_unknown}';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/unknown.gif" border="0" alt="' . $name . '" />';
			}    
		}
		else
		{
			if ($this->is_webbrowser('ie'))
			{
				$name = 'ie';
				$real = 'Internet Explorer';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ie.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('opera'))
			{
				$name = 'opera';
				$real = 'Opera';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/opera.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('firefox') OR $this->is_webbrowser('firebird') OR $this->is_webbrowser('phoenix'))
			{
				$name = 'firefox';
				$real = 'FireFox';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/firefox.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('camino'))
			{
				$name = 'camino';
				$real = 'Camino';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/camino.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('konqueror'))
			{
				$name = 'konqueror';
				$real = 'Konqueror';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/konqueror.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('chrome'))
			{
				$name = 'chrome';
				$real = 'Chrome';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/chrome.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('safari'))
			{
				$name = 'safari';
				$real = 'Safari';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/safari.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('netscape'))
			{
				$name = 'netscape';
				$real = 'Netscape';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/netscape.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('webtv'))
			{
				$name = 'webtv';
				$real = 'WebTV';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/webtv.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('lynx'))
			{
				$name = 'lynx';
				$real = 'Lynx';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/lynx.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('omniweb'))
			{
				$name = 'omniweb';
				$real = 'Omniweb';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/omniweb.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('icab'))
			{
				$name = 'icab';
				$real = 'iCab';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/icab.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('mspie'))
			{
				$name = 'mspie';
				$real = 'mspie';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mspie.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('netpositive'))
			{
				$name = 'netpositive';
				$real = 'NetPositive';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/netpositive.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('galeon'))
			{
				$name = 'galeon';
				$real = 'Galeon';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/galeon.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('iphone'))
			{
				$name = 'iphone';
				$real = 'iPhone';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/iphone.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('ipad'))
			{
				$name = 'ipad';
				$real = 'iPad';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ipad.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('blackberry'))
			{
				$name = 'blackberry';
				$real = 'BlackBerry';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blackberry.gif" border="0" alt="' . $real . '" />';
			}
			else if ($this->is_webbrowser('android'))
			{
				$name = 'android';
				$real = 'Android';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/android.gif" border="0" alt="' . $real . '" />';
			}
			else
			{
				$name = 'unknown';
				$real = '{_unknown}';
				$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/unknown.gif" border="0" alt="' . $real . '" />';
			}    
		}
		
		if (isset($showicon) AND $showicon)
		{
			return $icon;   
		}
                
		return $name;
	}
	
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>