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
* SMTP class to perform the simple mail transfer protocol operations within ILance.
*
* @package      iLance\SMTP
* @version      4.0.0.8059
* @author       ILance
*/
class smtp
{
        /**
        * SMTP hostname
        */
        var $smtpHost;
        
        /**
        * SMTP port number
        */
        var $smtpPort;
        
        /**
        * SMTP user name
        */
        var $smtpUser;
        
        /**
        * SMTP password
        */
        var $smtpPass;
        
        /**
        * Debug mode 
        */
        var $debug = false;
        
        /**
        * SMTP socket resource
        */
        var $smtpSocket = null;
        var $smtpReturn = 0;
        
        /**
        * Constructor
        */
        function smtp()
        {
			 global $ilconfig;
                $this->smtpHost = trim((($ilconfig['globalserversmtp_usetls'] == '1') ? 'tls://' : '') . $ilconfig['globalserversmtp_host']);
                $this->smtpPort = (!empty($ilconfig['globalserversmtp_port']) ? intval($ilconfig['globalserversmtp_port']) : 25);
                $this->smtpUser = $ilconfig['globalserversmtp_user'];
                $this->smtpPass = $ilconfig['globalserversmtp_pass'];
                $this->delimiter = "\r\n";
        }
        
        /**
        * Function to send the message
        */
        function sendMessage($msg, $expectedResult = false)
        {
                if ($msg !== false && !empty($msg))
                {
                        fputs($this->smtpSocket, $msg . "\r\n");
                }
                if ($expectedResult !== false)
                {
                        $result = '';
                        while ($line = @fgets($this->smtpSocket, 1024))
                        {
                                $result .= $line;
                                if (preg_match('#^(\d{3}) #', $line, $matches))
                                {
                                        break;
                                }
                        }
                        $this->smtpReturn = intval($matches[1]);
                        return ($this->smtpReturn == $expectedResult);
                }
                
                return true;
        }
        
        /**
        * Sets a user defined error message to be printed to the browser
        */
        function errorMessage($msg)
        {
                if ($this->debug)
                {
                        trigger_error($msg, E_USER_WARNING);
                }
                
                return false;
        }
        
        /**
        * Function to dispatch the email
        */
        function send()
        {
                global $ilance, $show;
                
                if (!$this->toemail)
                {
                        return false;
                }
                
                ($apihook = $ilance->api('datamanager_smtp_email_send_start')) ? eval($apihook) : false;
                
                $this->smtpSocket = fsockopen($this->smtpHost, $this->smtpPort, $errno, $errstr, 30);
                if ($this->smtpSocket)
                {
                        if (!$this->sendMessage(false, 220))
                        {
                                return $this->errorMessage('Unexpected response when connecting to SMTP server');
                        }
                        
                        if ($this->smtpUser AND $this->smtpPass)
                        {
                                if (!$this->sendMessage('EHLO ' . $this->smtpHost, 250))
                                {
                                        return $this->errorMessage('Unexpected response from SMTP server during handshake');
                                }
                                if ($this->sendMessage('AUTH LOGIN', 334))
                                {
                                        if (!$this->sendMessage(base64_encode($this->smtpUser), 334) OR !$this->sendMessage(base64_encode($this->smtpPass), 235))
                                        {
                                                return $this->errorMessage('Authorization to the SMTP server failed');
                                        }
                                }
                        }
                        else if (!$this->sendMessage('HELO ' . $this->smtpHost, 250))
                        {
                                return $this->errorMessage('Unexpected response from SMTP server during handshake');
                        }
                        
                        if (!$this->sendMessage('MAIL FROM:<' . $this->fromemail . '>', 250))
                        {
                                return $this->errorMessage('Unexpected response from SMTP server during FROM address transmission');
                        }
                        
                        $addresses = explode(',', $this->toemail);
                        foreach ($addresses as $address)
                        {
                                if (!$this->sendMessage('RCPT TO:<' . trim($address) . '>', 250))
                                {
                                        return $this->errorMessage('Unexpected response from SMTP server during TO address transmission');
                                }
                        }
                        
                        if ($this->sendMessage('DATA', 354))
                        {
                                $this->sendMessage('Date: ' . date('r'), false);
                                $this->sendMessage('To: ' . $this->toemail, false);
                                $this->sendMessage(trim($this->headers), false); // trim to prevent double \r\n
                                $this->sendMessage('Subject: ' . $this->subject, false);
                                $this->sendMessage("\r\n", false);
                                $this->sendMessage($this->message, false);
                        }
                        else
                        {
                                return $this->errorMessage('Unexpected response from SMTP server during data transmission');
                        }
            
                        if (!$this->sendMessage('.', 250))
                        {
                                return $this->errorMessage('Unexpected response from SMTP server when ending transmission');
                        }
                        
                        $this->sendMessage('QUIT', 221);
                        fclose($this->smtpSocket);
                        
                        ($apihook = $ilance->api('datamanager_smtp_email_send_end')) ? eval($apihook) : false;
                        
                        return true;
                }
                else
                {
                        return $this->errorMessage('Unable to connect to SMTP server');
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>