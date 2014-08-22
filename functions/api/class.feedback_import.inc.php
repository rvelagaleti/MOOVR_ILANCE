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
* Feedback import class to perform the majority of feedback import tools from external sites into ILance.
*
* @package      iLance\Feedback\Import
* @version      4.0.0.8059
* @author       ILance
*/
class feedback_import extends feedback
{
        /*
        * Function to verify eBay by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_ebay($username, $code)
        {
                $codecheck = md5($code);
                $ebayaboutmelink = "http://members.ebay.com/ws/eBayISAPI.dll?ViewUserPage&userid=" . $username;
                $ebayfile = fopen($ebayaboutmelink, "r");
                if ($ebayfile)
                {
                        while (!feof($ebayfile))
                        {
                                $ebaybuffer = fgets($ebayfile, 4096);
                                $ebayaboutmepage .= $ebaybuffer;
                        }
                        fclose($ebayfile);
                }
                else
                { 
                        $result = "1"; // cant connect
                }
                if (preg_match("/\b".$codecheck."\b/i", $ebayaboutmepage))
                { 
                        $result = "2"; // found
                }
                else
                {
                        $result = "0"; // not exist
                }
                return $result;
	}
        
        /*
        * Function to verify Yahoo by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_yahoo($username, $code)
        {
                $codecheck = md5($code);
                $yahooaboutmelink = "http://user.auctions.yahoo.com/show/aboutme?userID=" . $username;
                $yahoofile = fopen($yahooaboutmelink, "r");
                if ($yahoofile)
                {
                        while (!feof($yahoofile))
                        {
                                $yahoobuffer = fgets($yahoofile, 4096);
                                $yahooaboutmepage .= $yahoobuffer;
                        }
                        fclose($yahoofile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect
                }
                if (preg_match("/\b" . $codecheck . "\b/i", $yahooaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify emarket.gr by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_emarketGR($username, $code)
        {
                $codecheck = md5($code);
                $emarketaboutmelink = "http://www.emarket.gr/profile.php?user_id=" . $username;
                $emarketfile = fopen($emarkettmelink,"r");
                if ($emarketfile)
                {
                        while (!feof($emarketfile))
                        {
                                $emarketbuffer = fgets($emarketfile, 4096);
                                $emarketaboutmepage .= $emarketbuffer;
                        }
                        fclose($emarketfile);
                }
                else
                { 
                        $result = "1"; // cant connect
                }
                if (preg_match("/\b" . $codecheck . "\b/i", $emarketaboutmepage))
                {
                        $result = "2"; // found
                }
                else
                {
                        $result = "0"; // not present
                }
                return $result;
	}
        
        /*
        * Function to verify ibid.gr by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_ibidGR($username, $code)
        {
                $codecheck = md5($code);
                $ibidaboutmelink = "http://www.ibid.gr/ViewHomepage.asp?username=".$username;
                $ibidfile = fopen($emarkettmelink, "r");
                if ($ibidfile)
                {
                        while (!feof($ibidfile))
                        {
                                $ibidbuffer = fgets($ibidfile, 4096);
                                $ibidaboutmepage .= $ibidbuffer;
                        }
                        fclose($ibidfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect
                }
                if (preg_match("/\b".$codecheck."\b/i",$ibidaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
        }
        
        /*
        * Function to verify Bonanzle by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_bonanzle($username, $code)
        {
                $codecheck = md5($code);
                $bvaboutmelink = "http://www.bonanzle.com/booths/more_details/" . $username;
                $bvfile = fopen($bvaboutmelink, "r");
                if ($bvfile)
                {
                        while (!feof($bvfile))
                        {
                                $bvbuffer = fgets($bvfile, 4096);
                                $bvaboutmepage .= $bvbuffer;
                        }
                        fclose($bvfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect
                }
                if (preg_match("/\b".$codecheck."\b/i",$bvaboutmepage))
                { 
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify Etsy by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_etsy($username, $code)
        {
                $codecheck = md5($code);
                $wagglepopaboutmelink = "http://" . $username . ".etsy.com";
                $wagglepopfile = fopen($wagglepopaboutmelink, "r");
                if ($wagglepopfile)
                {
                        while (!feof($wagglepopfile))
                        {
                                $wagglepopbuffer = fgets($wagglepopfile, 4096);
                                $wagglepopaboutmepage .= $wagglepopbuffer;
                        }
                        fclose($wagglepopfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect
                }
                if (preg_match("/\b".$codecheck."\b/i", $wagglepopaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify iOffer by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_ioffer($username, $code)
        {
                $codecheck = md5($code);
                $bidalotaboutmelink = "http://www.ioffer.com/users/".$username;
                $bidalotfile = fopen($bidalotaboutmelink,"r");
                if ($bidalotfile)
                {
                        while (!feof($bidalotfile))
                        {
                                $bidalotbuffer = fgets($bidalotfile, 4096);
                                $bidalotaboutmepage .= $bidalotbuffer;
                        }
                        fclose($bidalotfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect 
                }
                if (preg_match("/\b".$codecheck."\b/i",$bidalotaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify Overstock by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_overstock($username, $code)
        {
                $codecheck = md5($code);
                $overstockaboutmelink = "http://auctions.overstock.com/cgi-bin/auctions.cgi?PAGE=MYHOME&USRID=" . $username;
                $overstockfile = fopen($overstockaboutmelink, "r");
                if ($overstockfile)
                {
                        while (!feof($overstockfile))
                        {
                                $overstockbuffer = fgets($overstockfile, 4096);
                                $overstockaboutmepage .= $overstockbuffer;
                        }
                        fclose($overstockfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect 
                }
                if (preg_match("/\b".$codecheck."\b/i",$overstockaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify Amazon by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_amazon($username, $code)
        {
                $codecheck = md5($code);
                $amazonaboutmelink = "http://s1.amazon.com/exec/varzea/ts/user-glance/" . $username . "/002-6321671-7004064";
                $amazonfile = fopen($amazonaboutmelink, "r");
                if ($amazonfile)
                {
                        while (!feof($amazonfile))
                        {
                                $amazonbuffer = fgets($amazonfile, 4096);
                                $amazonaboutmepage .= $amazonbuffer;
                        }
                        fclose($amazonfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect 
                }
                if (preg_match("/\b".$codecheck."\b/i",$amazonaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify eBid.com by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_ebid($username, $code)
        {
                $codecheck = md5($code);
                $ebidaboutmelink = "http://uk.nine.ebid.net/perl/normal.cgi?user=$username&mo=their-all-about";
                $ebidfile = fopen($ebidaboutmelink, "r");
                if ($ebidfile)
                {
                        while (!feof($ebidfile))
                        {
                                $ebidbuffer = fgets($ebidfile, 4096);
                                $ebidaboutmepage .= $ebidbuffer;
                        }
                        fclose($ebidfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect 
                }
                if (preg_match("/\b".$codecheck."\b/i",$ebidaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify eBid.us by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_ebidus($username, $code)
        {
                $codecheck = md5($code);
                $ebidaboutmelink = "http://us.nine.ebid.net/perl/normal.cgi?user=$username&mo=their-all-about";
                $ebidfile = fopen($ebidaboutmelink, "r");
                if ($ebidfile)
                {
                        while (!feof($ebidfile))
                        {
                                $ebidbuffer = fgets($ebidfile, 4096);
                                $ebidaboutmepage .= $ebidbuffer;
                        }
                        fclose($ebidfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect 
                }
                if (preg_match("/\b".$codecheck."\b/i",$ebidaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to verify ricardo.gr by connecting to the site and verifying a special code
        *
        * @param        string      username on external site
        * @param        string      special code we should expect to find
        *
        * @return	string      Returns 0, 1 or 2
        */
        function verify_ricardo($username, $code)
        {
                $codecheck = md5($code);
                $overstockaboutmelink = "http://www.ricardo.gr/accdb/viewUser.asp?IDU=".$username;
                $overstockfile = fopen($overstockaboutmelink,"r");
                if ($overstockfile)
                {
                        while (!feof($overstockfile))
                        {
                                $overstockbuffer = fgets($overstockfile, 4096);
                                $overstockaboutmepage .= $overstockbuffer;
                        }
                        fclose($overstockfile);
                }
                else
                { 
                        $result = "1"; // Error cant Connect 
                }
                if (preg_match("/\b".$codecheck."\b/i",$overstockaboutmepage))
                {
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // Code not Present
                }
                return $result;
	}
        
        /*
        * Function to fetch eBay feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
        function get_ebay_feedback_score($username)
        {
                $user = $this->clean($username);
                $url = 'http://feedback.ebay.com/ws/eBayISAPI.dll?ViewFeedback2&userid=' . $username . '&ftab=AllFeedback&myworld=true';
                $contents = @file_get_contents($url);
                if (!$contents) // no url fopen, switch to curl
                {
                        if (function_exists('curl_init'))
                        {
                                $web = curl_init();
                                curl_setopt($web, CURLOPT_URL,$url);
                                curl_setopt($web, CURLOPT_HEADER, 0);
                                $contents = curl_exec($web);
                                curl_close($web);
                        }
                }
                if (!$contents)
                {
                        echo 'cannot get url';
                        return false;
                  
                }
                else
                {
                        preg_match("/<div class=.mbg.>(.+?)$username(.+?)\(\s(\d+)\b(.*?)<\/div>/i", $contents, $arr);
                        return $arr[3];
                }
        }
        
        /*
        * Function to fetch Yahoo feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
        function get_yahoo_score($username)
        {
                $yahoofeedbacklink = "http://ratings.auctions.yahoo.com/show/rating?userID=" . $username;
                $yahoofilea = fopen($yahoofeedbacklink, "r");
                if ($yahoofilea)
                {
                        while (!feof($yahoofilea))
                        {
                                $yahoobuffer = fgets($yahoofilea, 4096);
                                $yahoofeedbackpage .= $yahoobuffer;
                        }
                        fclose($yahoofilea);
                }
                else
                { 
                        $result = "1"; // Error cant Connect
                }
                preg_match("/" . $username . "\s\((.*?)\)/i", $yahoofeedbackpage, $yahoomatch);
                $yahooresult = $yahoomatch[1];
                $result = strip_tags($yahooresult);
                if ($result > "")
                { 
                        $score = $result;
                        $result = "2"; // Found
                }
                else
                {
                        $result = "0"; // No Feedback
                }
                return $score;
	}
        
        /*
        * Function to fetch emarket.gr feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
        function get_emarketGR_score($username)
        {
                $site = fopen('http://www.emarket.gr/feedback.php?id='.$username.'&faction=show', 'r');
                while($cont = fread($site, 1024657))
                { 
                        $total .= $cont; 
                } 
                fclose($site); 
                $match_expression = '/<div style="color: #000000; line-height: 17px; font-weight: bold;">(.*)<\/div>/'; 
                preg_match($match_expression, $total, $matches); 
                $score = strip_tags($matches[1]);
                return $score;
	}
        
        /*
        * Function to fetch ibid.gr feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
        function get_ibidGR_score($username)
        {
                $site = fopen('http://www.ibid.gr/ViewFeedbackProfile2.asp?username=' . $username, 'r');
                while ($cont = fread($site, 1024657))
                { 
                        $total .= $cont; 
                } 
                fclose($site); 
                $match_expression = '/<div style="color: #000000; line-height: 17px; font-weight: bold;">(.*)<\/div>/'; 
                preg_match($match_expression, $total, $matches); 
                $score = strip_tags($matches[1]);
                return $score;
        }

	/*
        * Function to fetch bonanzle feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_bonanzle_score($username)
        {
                $site = fopen('http://www.bonanzle.com/booths/' . urlencode($username), 'r'); 
                while($cont = fread($site, 1024657))
                { 
                        $total .= $cont; 
                } 
                fclose($site);
                preg_match('~Feedback <a href="/users/[0-9]+/user_feedbacks" style="font-size:12px;">(.*?)</a>~is', $total, $matches);
                $result = $matches[1]; 
                if ($result > "")
                { 
                        $score = $result;
                }
                else
                {
                        $score = "No Feedback!";
                        $score = $result;
                }
                return $score;
	}

	/*
        * Function to fetch Etsy feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_etsy_score($username)
        {
                $site = fopen('http://www.etsy.com/shop/' . urlencode($username), 'r'); 
                while($cont = fread($site, 1024657))
                { 
                        $total .= $cont; 
                } 
                fclose($site);
                preg_match('~<em class="feedback">(.*?),~is', $total, $matches);
                return $matches[1]; 
	}

	/*
        * Function to fetch iOffer feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_ioffer_score($username)
        { 
                $site = fopen('http://www.ioffer.com/ratings/' . urlencode($username), 'r'); 
                while($cont = fread($site, 1024657))
                { 
                        $total .= $cont; 
                } 
                fclose($site);
                preg_match('~name="&amp;lid=//Lower Header//Rating" style="display: inline; font-size: 11px; font-weight: bold; padding-left: 0px; padding-right: 0px">\((.*?)\)</a>~is', $total, $matches);
                $result = $matches[1];
                if ($result>"")
                {
                        $score = $result;
                }
                else
                {
                        $score = "No Feedback!";
                        $score = $result;
                }
                return $score;
	}

	/*
        * Function to fetch Overstock feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_overstock_score($username)
	{
                $URL = 'http://auctions.overstock.com/cgi-bin/auctions.cgi?PAGE=AUCTFDBK&USRID=' . $username;
                if (function_exists('curl_init'))
                {
                        $ch = curl_init();
                        @curl_setopt($ch, CURLOPT_URL, $URL);
                        @curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5.0.6");
                        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $content = curl_exec($ch);
                        curl_close($ch);
                }
                else
                {
                        $content = implode('', file($URL));
                }
                preg_match('@Business Rating Page" >(.*?)</a>\)@i', $content, $match);
                $score = $match[1];
                return $score;
	}

	/*
        * Function to fetch Amzaon feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_amazon_score($username)
	{
                $URL = 'http://www.amazon.com/gp/help/seller/home.html/002-6321671-7004064?%5Fencoding=UTF8&asin=&marketplaceSeller=1&seller='.$username;
                if (function_exists('curl_init'))
                {
                        $ch = curl_init();
                        @curl_setopt($ch, CURLOPT_URL, $URL);
                        @curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5.0.6");
                        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $content = curl_exec($ch);
                        curl_close($ch);
                }
                else
                {
                        $content = implode('', file($URL));
                }
                preg_match('@\(<b>(.*?)</b> rating@i', $content, $match);
                return $match[1];
	}

	/*
        * Function to fetch eBid feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_ebid_score($username)
	{
                $URL = "http://uk.nine.ebid.net/perl/normal.cgi?user=$username&mo=user-rating&type=feed";
                if (function_exists('curl_init'))
                {
                        $ch = curl_init();
                        @curl_setopt($ch, CURLOPT_URL, $URL);
                        @curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5.0.6");
                        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $content = curl_exec($ch);
                        curl_close($ch);
                }
                else
                {
                        $content = implode('', file($URL));
                }
                preg_match('@>(.*?) separate comments<@i', $content, $match);
                $score = strip_tags($match[1]);
                return $score;
	}

	/*
        * Function to fetch eBid.us feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_ebid_scoreus($username)
	{
                $URL = "http://us.nine.ebid.net/perl/normal.cgi?user=$username&mo=user-rating&type=feed";
                if (function_exists('curl_init'))
                {
                        $ch = curl_init();
                        @curl_setopt($ch, CURLOPT_URL, $URL);
                        @curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.6) Gecko/20060728 Firefox/1.5.0.6");
                        @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $content = curl_exec($ch);
                        curl_close($ch);
                }
                else
                {
                        $content = implode('', file($URL));
                }
                preg_match('@>(.*?) separate comments<@i', $content, $match);
                $score = strip_tags($match[1]);
                return $score;
	}

	/*
        * Function to fetch ricardo.gr feedback score by connecting to the site and supplying a username
        *
        * @param        string      username on external site
        *
        * @return	string      Returns feedback score number
        */
	function get_ricardo_score($username)
	{
                $site = fopen('http://www.ricardo.gr/accdb/ViewUser.asp?IDU=' . urlencode($username), 'r'); 
                while($cont = fread($site, 1024657))
                { 
                        $total .= $cont; 
                } 
                fclose($site); 
                $match_expression = '/<td width="35%"><b>(.*)<\/b><\/td>/'; 
                preg_match($match_expression, $total, $matches); 
                $score = strip_tags($matches[1]);
                return $score;
	}
              
        function clean($string)
        {
                $new_string = strip_tags(trim($string));
                $new_string = preg_replace("/[^_0-9a-z\-\.]+/", "", $new_string);
                return strtolower($new_string);
        }
        
        function print_imported_feedback($userid = 0, $section = 'feedback')
        {
                global $ilance, $ilconfig, $show;
                $html = '';
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "feedback_import
                        WHERE userid = '" . intval($userid) . "'
                        LIMIT 1
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        if ($section == 'feedback')
                        {
                                $html .= '<div style="margin-left:15px;padding-bottom:6px;padding-top:15px"><span style="font-size:13px"><strong>{_imported_feedback}</strong></span>
                                <div style="padding-bottom:6px;"></div>';
                                $html .= (($res['fb_ebay'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://feedback.ebay.com/ws/eBayISAPI.dll?ViewFeedback2&userid=' . $res['id_ebay'] . '&ftab=AllFeedback&myworld=true" rel="nofollow" target="_blank">eBay</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ebay.png" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_ebay']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_ebay'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_ebay'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_yahoo'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://ratings.auctions.yahoo.com/show/rating?userID=' . $res['id_yahoo'] . '" rel="nofollow" target="_blank">Yahoo</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/yahoo.png" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_yahoo']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_yahoo'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_yahoo'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_emarket'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://www.emarket.gr/feedback.php?id=' . $res['id_emarket'] . '&faction=show" rel="nofollow" target="_blank">eMarket.gr</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/emarketgr.gif" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_emarket']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_emarket'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_emarket'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_bonanzle'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://www.bonanzle.com/booths/' . $res['id_bonanzle'] . '" rel="nofollow" target="_blank">Bonanzle</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bonanzle.gif" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_bonanzle']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_bonanzle'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_bonanzle'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_etsy'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://www.etsy.com/shop/' . $res['id_etsy'] . '" rel="nofollow" target="_blank">Etsy</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/etsy.png" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_etsy']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_etsy'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_etsy'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_ioffer'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://www.ioffer.com/ratings/' . $res['id_ioffer'] . '" rel="nofollow" target="_blank">iOffer</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ioffer.gif" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_ioffer']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_ioffer'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_ioffer'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_overstock'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://auctions.overstock.com/cgi-bin/auctions.cgi?PAGE=AUCTFDBK&USRID=' . $res['id_overstock'] . '" rel="nofollow" target="_blank">Overstock</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/overstock.gif" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_overstock']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_overstock'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_overstock'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_ricardo'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://www.ricardo.gr/accdb/ViewUser.asp?IDU=' . $res['id_recardo'] . '" rel="nofollow" target="_blank">Ricardo</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ricardo.gif" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_ricardo']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_ricardo'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_ricardo'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_amazon'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://www.amazon.com/gp/help/seller/home.html/002-6321671-7004064?%5Fencoding=UTF8&asin=&marketplaceSeller=1&seller=' . $res['id_amazon'] . '" rel="nofollow" target="_blank">Amazon</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amazon.png" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_amazon']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_amazon'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_amazon'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_ebidus'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://us.nine.ebid.net/perl/normal.cgi?user=' . $res['id_ebidus'] . '&mo=user-rating&type=feed" rel="nofollow" target="_blank">eBid.us</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ebid.gif" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_ebidus']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_ebidus'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_ebidus'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= (($res['fb_ebid'] > 0) ? '<div style="padding-top:13px"><span class="blue" style="float:left;width:105px"><a href="http://uk.nine.ebid.net/perl/normal.cgi?user=' . $res['id_ebid'] . '&mo=user-rating&type=feed" rel="nofollow" target="_blank">eBid.uk</a><span style="float:left;padding-right:7px;margin-top:-3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ebid.gif" border="0" alt="" /></span></span>&nbsp;<span style="font-size:15px"><strong>' . number_format($res['fb_ebid']) . '</strong><span class="smaller litegray" title="{_last_updated} ' . print_date($res['dv_ebid'], 'D jS M \'Y') . '">&nbsp;&nbsp;(' . print_date($res['dv_ebid'], 'm/j/Y') . ')</span></span></div>' : '');
                                $html .= '</div>';
                        }
                        else if ($section == 'userbit')
                        {
                                $html .= (($res['fb_ebay'] > 0) ? '<span title="eBay score: ' . number_format($res['fb_ebay']) . ' (' . print_date($res['dv_ebay'], 'm/j/Y') . ')"><a href="http://feedback.ebay.com/ws/eBayISAPI.dll?ViewFeedback2&userid=' . $res['id_ebay'] . '&ftab=AllFeedback&myworld=true" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ebay.png" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_yahoo'] > 0) ? '<span title="Yahoo score: ' . number_format($res['fb_yahoo']) . ' (' . print_date($res['dv_yahoo'], 'm/j/Y') . ')"><a href="http://ratings.auctions.yahoo.com/show/rating?userID=' . $res['id_yahoo'] . '" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/yahoo.png" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_emarket'] > 0) ? '<span title="eMarket score: ' . number_format($res['fb_emarket']) . ' (' . print_date($res['dv_emarket'], 'm/j/Y') . ')"><a href="http://www.emarket.gr/feedback.php?id=' . $res['id_emarket'] . '&faction=show" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/emarketgr.gif" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_bonanzle'] > 0) ? '<span title="Bonanzle score: ' . number_format($res['fb_bonanzle']) . ' (' . print_date($res['dv_bonanzle'], 'm/j/Y') . ')"><a href="http://www.bonanzle.com/booths/' . $res['id_bonanzle'] . '" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bonanzle.gif" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_etsy'] > 0) ? '<span title="Etsy score: ' . number_format($res['fb_etsy']) . ' (' . print_date($res['dv_etsy'], 'm/j/Y') . ')"><a href="http://www.etsy.com/shop/' . $res['id_etsy'] . '" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/etsy.png" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_ioffer'] > 0) ? '<span title="iOffer score: ' . number_format($res['fb_ioffer']) . ' (' . print_date($res['dv_ioffer'], 'm/j/Y') . ')"><a href="http://www.ioffer.com/ratings/' . $res['id_ioffer'] . '" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ioffer.gif" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_overstock'] > 0) ? '<span title="Overstock score: ' . number_format($res['fb_overstock']) . ' (' . print_date($res['dv_overstock'], 'm/j/Y') . ')"><a href="http://auctions.overstock.com/cgi-bin/auctions.cgi?PAGE=AUCTFDBK&USRID=' . $res['id_overstock'] . '" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/overstock.gif" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_ricardo'] > 0) ? '<span title="Ricardo score: ' . number_format($res['fb_ricardo']) . ' (' . print_date($res['dv_ricardo'], 'm/j/Y') . ')"><a href="http://www.ricardo.gr/accdb/ViewUser.asp?IDU=' . $res['id_recardo'] . '" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ricardo.gif" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_amazon'] > 0) ? '<span title="Amazon score: ' . number_format($res['fb_amazon']) . ' (' . print_date($res['dv_amazon'], 'm/j/Y') . ')"><a href="http://www.amazon.com/gp/help/seller/home.html/002-6321671-7004064?%5Fencoding=UTF8&asin=&marketplaceSeller=1&seller=' . $res['id_amazon'] . '" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amazon.png" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_ebidus'] > 0) ? '<span title="eBid.us score: ' . number_format($res['fb_ebidus']) . ' (' . print_date($res['dv_ebidus'], 'm/j/Y') . ')"><a href="http://us.nine.ebid.net/perl/normal.cgi?user=' . $res['id_ebidus'] . '&mo=user-rating&type=feed" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ebid.gif" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                                $html .= (($res['fb_ebid'] > 0) ? '<span title="eBid.uk score: ' . number_format($res['fb_ebid']) . ' (' . print_date($res['dv_ebid'], 'm/j/Y') . ')"><a href="http://uk.nine.ebid.net/perl/normal.cgi?user=' . $res['id_ebid'] . '&mo=user-rating&type=feed" rel="nofollow" target="_blank"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ebid.gif" border="0" alt="" style="vertical-align:middle;margin-top:-5px;padding-right:4px;padding-left:4px" /></a></span>' : '');
                        }

                }
                return $html;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>