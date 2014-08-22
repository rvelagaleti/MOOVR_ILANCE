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
* Password helper functions for iLance
*
* @package      iLance\Global\Password
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to generate a unique password salt string mainly used for password hashing
*
* @param       integer      length of salt to generate
* 
* @return      string       Salt string
*/
function construct_password_salt($length = 5)
{
        $salt = '';
        for ($i = 0; $i < $length; $i++)
        {
                $salt .= chr(rand(33, 126));
        }
        $salt = str_replace(",", "_", $salt);
        $salt = str_replace("'", "^", $salt);
        $salt = str_replace('"', '*', $salt);
        $salt = str_replace("\\", '+', $salt);
        $salt = str_replace("\\\\", '-', $salt);
        return $salt;
}

/**
* Function to generate a human-readable password where the password length is based on a supplied argument
*
* @param	integer      password character length
*
* @return	string       Generated human-readable password
*/
function construct_password($len = 8)
{
	error_reporting(0);
        $vocali = array('a','e','i','o','u');
        $dittonghi = array('ae','ai','ao','au','ea','ei','eo','eu','ia','ie','io','iu','ua','ue','ui','uo');
        $cons = array('b','c','d','f','g','h','k','l','n','m','p','r','s','t','v','z');
        $consdoppie = array('bb','cc','dd','ff','gg','ll','nn','mm','pp','rr','ss','tt','vv','zz');
        $consamiche = array('bl','br','ch','cl','cr','dl','dm','dr','fl','fr','gh','gl','gn','gr','lb','lp','ld','lf','lg','lm','lt','lv','lz','mb','mp','nd','nf','ng','nt','nv','nz','pl','pr','ps','qu','rb','rc','rd','rf','rg','rl','rm','rn','rp','rs','rt','rv','rz','sb','sc','sd','sf','sg','sl','sm','sn','sp','sr','st','sv','tl','tr','vl','vr');
        $listavocali = array_merge($vocali, $dittonghi);
        $listacons = array_merge($cons, $consdoppie, $consamiche);
        $nrvocali = sizeof($listavocali);
        $nrconsonanti = sizeof($listacons);
        $loop = $len;
        $password = '';
        if (rand(1, 10) > 5)
        {
                $password = $cons[rand(1, sizeof($cons))];
                $password .= $listavocali[rand(1, $nrvocali)];
                $inizioc = true;
                $loop--;
        }
        for ($i = 0; $i < $loop; $i++)
        {
                $qualev = $listavocali[rand(1, $nrvocali)];
                $qualec = $listacons[rand(1, $nrconsonanti)];
                if (isset($inizioc))
                {
                        $password .= $qualec . $qualev;
                }
                else
                {
                        $password .= $qualev . $qualec;
                }
        }
        $password = mb_substr($password, 0, $len);
        if (in_array(mb_substr($password, ($len - 2), $len), $consdoppie))
        {
                $password = mb_substr($password, 0, ($len - 1)) . $listavocali[rand(1, $nrvocali)];
        }
        return $password;
}

/**
* Function to verify a password and show a prompt or an error message
*
* @param	string       password
* @param        string       return url
* @param        string       key
* @param        string       value
* @param        integer      timeout value
*
* @return	string       HTML output
*/
function verify_password_prompt($password = '', $returnurl = '', $key = 'cardupdate', $value = '', $timeout = 120)
{
	$badpassword = false;
	if ($_SESSION['ilancedata']['user']['password'] != iif($password, md5(md5($password) . $_SESSION['ilancedata']['user']['salt']), '') AND $_SESSION['ilancedata']['user']['password'] != md5(md5($password) . $_SESSION['ilancedata']['user']['salt']))
	{
		$badpassword = true;
	}
	// check for cookie so we don't need to re-enter password
	if ($badpassword)
	{
		print_notice('{_account_password_incorrect}', '{_there_was_a_problem_verifying_the_password}', urldecode($returnurl), '{_retry}');
		exit();
	}
	else
	{
		$_SESSION['ilancedata']['user'][$key . '_' . $value] = TIMESTAMPNOW + $timeout;
		refresh(urldecode($returnurl));
		exit();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>