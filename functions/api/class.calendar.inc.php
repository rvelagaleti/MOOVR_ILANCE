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
* Calendar class to perform the majority of ILance calendar functions within ILance.
*
* @package      iLance\Calendar
* @version      4.0.0.8059
* @author       ILance
*/
class calendar
{
        var $year;
        var $month;
        var $day;
        var $monthNameFull;
        var $monthNameBrief;
        var $startDay;
        var $endDay;
        var $events;
        
        /**
        * Function to generate the calendar HTML widget
        *
        * @param        integer       year
        * @param        integer       month
        * 
        * @return       object
        */
        function generate_calendar($yr, $mo, $da)
        {
                $this->year = $yr;
                $this->month = (int) $mo;
                $this->day = $da;
        
                $this->startTime = strtotime("$yr-$mo-01 00:00");
        
                $this->endDay = date('t', $this->startTime);
                $this->endTime = strtotime("$yr-$mo-".$this->endDay." 23:59");
        
                $this->startDay = date('D', $this->startTime);
                $this->startOffset = date('w', $this->startTime) - 1;
        
                if ($this->startOffset < 0)
                {
                        $this->startOffset = 6;
                }
        
                $this->monthNameFull = strftime('%B', $this->startTime);
                $this->monthNameBrief= strftime('%b', $this->startTime);
        
                $this->dayNameFmt = '%a';
                $this->tblWidth="*";
        }
    
        /**
        * Function to get the start time of the calendar
        */
        function getStartTime()
        {
                return $this->startTime;
        }
    
        /**
        * Function to get the end time of the calendar
        */
        function getEndTime()
        {
                return $this->endTime;
        }
    
        /**
        * Function to get the year of the calendar
        */
        function getYear()
        {
                return $this->year;
        }
    
        /**
        * Function to get the full month name
        */
        function getFullMonthName()
        {
                return $this->monthNameFull;
        }
    
        /**
        * Function to get the brief month name
        */
        function getBriefMonthName()
        {
                return $this->monthNameBrief;
        }
    
        /**
        * Function to set the calendar table width
        */
        function setTableWidth($w)
        {
                $this->tblWidth = $w;
        }
    
        /**
        * Function to set the year on the calendar
        */
        function setYear($year)
        {
                $this->year = $year;
        }
    
        /**
        * Function to set the month on the calendar
        */
        function setMonth($month)
        {
                $this->month = $month;
        }
        
        /**
        * Function to set the day-name format on the calendar (any valid strftime format for display weekday names).
        *
        * @param      string     Example: %a - abbreviated, %A - full, %u as number with 1==Monday
        */
        function setDayNameFormat($f)
        {
                $this->dayNameFmt = $f;
        }
        
        /**
        * Returns markup for displaying the calendar.
        */
        function display()
        {
                global $ilance;
                
                $html = '';
                $html .= $this->dspDayNames();
                $html .= $this->dspDayCells();
        
                return $html;
        }
        
        /**
        * Displays the row of day names.
        */
        function dspDayNames()
        {
                $names = array('2009-06-01','2009-06-02','2009-06-03','2009-06-04','2009-06-05','2009-06-06','2009-06-07',);
        
                $html = '<tr class="alt2">';
        
                for ($i = 0; $i < 7; $i++)
                {
                        $html .= '<td width="14%">' . strftime($this->dayNameFmt, strtotime($names[$i])) . '</td>';
                }
                
                $html .= '</tr>';
                
                return $html;
        }
    
        /**
        * Displays all day cells for the month
        */
        function dspDayCells()
        {
                $i = 0; // cell counter
        
                $html = '<tr>';
                
                // first display empty cells based on what weekday the month starts in]
                for ($c = 0; $c < $this->startOffset; $c++)
                {
                        $i++;
                        $html .= '<td class="calendarnotinmonth">&nbsp;</td>';
                }
        
                // write out the rest of the days, at each sunday, start a new row.
                for ($d = 1; $d <= $this->endDay; $d++)
                {
                        $i++;
            
                        $html .= $this->dspDayCell($d);
                        
                        if ( $i%7 == 0 )
                        {
                                $html .= '</tr>';
                        }
            
                        if ($d < $this->endDay && $i%7 == 0)
                        {
                                $html .= '<tr>';
                        }
                }
        
                // fill in the final row
                $left = 7 - ($i%7);
        
                if ($left < 7)
                {
                        for ($c = 0; $c < $left; $c++)
                        {
                                $html .= '<td class="calendarnotinmonth">&nbsp;</td>';
                        }
                        
                        $html .= "\n\t</tr>";
                }
        
                return $html;
        }
    
        /**
        * Function to output the contents for a given day
        *
        * @param        string       day
        */
        function dspDayCell($day)
        {
                if ($day == date("d"))
                {
                        return '<td class="calendartd2 alt2"><strong>'.$this->dspDayCellInfo($day).'</strong></td>';
                }
                else
                {
                        return '<td class="calendartd alt1">'.$this->dspDayCellInfo($day).'</td>';
                }        
        }
        
        /**
        * Function to display cell information for a paricular day on the calendar
        *
        * @param        string       day
        */
        function dspDayCellInfo($day)
        {
                if ($events = $this->getDaysEvents($day))
                {
                        $html  = '<div class="dayNum" style="font-size:17px"><span class="blue"><strong>'.$day.'</strong></span></div>';
                        foreach ($events AS $i => $e)
                        {
                                if (empty($e['link']))
                                {
                                        $htmlbit = $e['title'];	
                                }
                                else 
                                {
                                        $htmlbit = '<span class="blue"><a href="'.$e['link'].'">'.$e['title'].'</a></span>';
                                }
                                
                                $html .= '<div style="text-align: left; padding:5px"><span class="smaller">'.$htmlbit.'</span></div>';
                        }
                }
                else
                {
                        $html = '<p class="dayNumNoEvents gray" style="font-size:17px">'.$day.'</p>';
                }
                
                return $html;
        }
    
        /**
        * Adds an event on the calendar for a specific day
        *
        * @param        string       day
        * @param        string       title
        * @param        string       link
        */
        function addEvent($day, $title, $link = '')
        {
                $this->events[(int)$day][] = array('title' => $title, 'link' => $link);
        }
    
        /**
        * Returns an array of the events on a day.
        *
        * @param        string       day
        */
        function getDaysEvents($day)
        {
                if (@count($this->events[$day]) > 0)
                {
                        return $this->events[$day];
                }
                else
                {
                        return false;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>