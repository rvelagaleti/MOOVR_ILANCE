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
* Timer class to debug how long a function takes within ILance
*
* @package      iLance\Timer
* @version      4.0.0.8059
* @author       ILance
*/
class timer
{
        var $stime;
        var $etime;
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function timer()
        {
                $this->stime = 0.0;
        }
       
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function get_microtime()
        {
                $tmp = explode(" ",microtime());
                $rtime = (double)$tmp[0] + (double)$tmp[1];
                return $rtime;
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function start()
        {
                $this->stime = $this->get_microtime();
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function stop()
        {
                $this->etime = $this->get_microtime();
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function get($decimal = 4)
        {
                return round(($this->etime - $this->stime), $decimal);
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>