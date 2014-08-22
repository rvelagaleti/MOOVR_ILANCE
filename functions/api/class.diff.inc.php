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
error_reporting(0);

/**
* Class to Find the difference between two strings
*
* @package      iLance\Diff
* @version      4.0.0.8059
* @author       ILance
*/
class diff
{
	var $data_old = array();
	var $data_old_len = 0;
	var $data_new = array();
	var $data_new_len = 0;
	var $table = array();
	/**
        * Constructor
        *
        */
	function diff($data_old, $data_new)
	{
		$this->data_old = preg_split('#(\r\n|\n|\r)#', $data_old);
		$this->data_old_len = sizeof($this->data_old);
		$this->data_new = preg_split('#(\r\n|\n|\r)#', $data_new);
		$this->data_new_len = sizeof($this->data_new);
	}
	/**
        * ...
        *
        */
	function populate_table()
	{
		$this->table = array();
		$prev_row = array();
		for ($i = -1; $i < $this->data_new_len; $i++)
		{
			$prev_row[$i] = 0;
		}
		for ($i = 0; $i < $this->data_old_len; $i++)
		{
			$this_row = array('-1' => 0);
			$data_old_value = $this->data_old[$i];
			for ($j = 0; $j < $this->data_new_len; $j++)
			{
				if ($data_old_value == $this->data_new[$j])
				{
					$this_row[$j] = $prev_row[$j - 1] + 1;
				}
				else if ($this_row[$j - 1] > $prev_row[$j])
				{
					$this_row[$j] = $this_row[$j - 1];
				}
				else
				{
					$this_row[$j] = $prev_row[$j];
				}
			}
			$this->table[$i - 1] = $this->compress_row($prev_row);
			$prev_row = $this_row;
		}
		unset($prev_row);
		$this->table[$this->data_old_len - 1] = $this->compress_row($this_row);
	}
	/**
        * ...
        *
        */
	function compress_row($row)
	{
		return implode('|', $row);
	}
	/**
        * ...
        *
        */
	function decompress_row($row)
	{
		$return = array();
		$i = -1;
		foreach (explode('|', $row) AS $value)
		{
			$return[$i] = $value;
			++$i;
		}
		return $return;
	}
	/**
        * ...
        *
        */
	function &fetch_table()
	{
		if (sizeof($this->table) == 0)
		{
			$this->populate_table();
		}
		return $this->table;
	}
	/**
        * ...
        *
        */
	function &fetch_diff()
	{
		$table =& $this->fetch_table();
		$output = array();
		$match = array();
		$nonmatch1 = array();
		$nonmatch2 = array();
		$data_old_key = $this->data_old_len - 1;
		$data_new_key = $this->data_new_len - 1;
		$this_row = $this->decompress_row($table[$data_old_key]);
		$above_row = $this->decompress_row($table[$data_old_key - 1]);
		while ($data_old_key >= 0 AND $data_new_key >= 0)
		{
			if ($this_row[$data_new_key] != $above_row[$data_new_key - 1] AND $this->data_old[$data_old_key] == $this->data_new[$data_new_key])
			{
				$this->process_nonmatches($output, $nonmatch1, $nonmatch2);
				array_unshift($match, $this->data_old[$data_old_key]);
				$data_old_key--;
				$data_new_key--;
				$this_row = $above_row;
				$above_row = $this->decompress_row($table[$data_old_key - 1]);
			}
			else if ($above_row[$data_new_key] > $this_row[$data_new_key - 1])
			{
				$this->process_matches($output, $match);
				array_unshift($nonmatch1, $this->data_old[$data_old_key]);
				$data_old_key--;
				$this_row = $above_row;
				$above_row = $this->decompress_row($table[$data_old_key - 1]);
			}
			else
			{
				$this->process_matches($output, $match);
				array_unshift($nonmatch2, $this->data_new[$data_new_key]);
				$data_new_key--;
			}
		}
		$this->process_matches($output, $match);
		if ($data_old_key > -1 OR $data_new_key > -1)
		{
			for (; $data_old_key > -1; $data_old_key--)
			{
				array_unshift($nonmatch1, $this->data_old[$data_old_key]);
			}
			for (; $data_new_key > -1; $data_new_key--)
			{
				array_unshift($nonmatch2, $this->data_new[$data_new_key]);
			}
			$this->process_nonmatches($output, $nonmatch1, $nonmatch2);
		}
		return $output;
	}
	/**
        * ...
        *
        */
	function process_matches(&$output, &$match)
	{
		if (sizeof($match) > 0)
		{
			$data = implode("\n", $match);
			array_unshift($output, new diff_entry($data, $data));
		}
		$match = array();
	}
	/**
        * ...
        *
        */
	function process_nonmatches(&$output, &$text_old, &$text_new)
	{
		$s1 = sizeof($text_old);
		$s2 = sizeof($text_new);
		if ($s1 > 0 AND $s2 == 0)
		{
			// lines deleted
			array_unshift($output, new diff_entry(implode("\n", $text_old), ''));
		}
		else if ($s2 > 0 AND $s1 == 0)
		{
			// lines added
			array_unshift($output, new diff_entry('', implode("\n", $text_new)));
		}
		else if ($s1 > 0 AND $s2 > 0)
		{
			// substitution
			array_unshift($output, new diff_entry(implode("\n", $text_old), implode("\n", $text_new)));
		}
		$text_old = array();
		$text_new = array();
	}
}

class diff_entry
{
	var $data_old = '';
	var $data_new = '';
	/**
        * ...
        *
        */
	function diff_entry($data_old, $data_new)
	{
		$this->data_old = $data_old;
		$this->data_new = $data_new;
	}
	/**
        * ...
        *
        */
	function fetch_data_old()
	{
		return $this->data_old;
	}
	/**
        * ...
        *
        */
	function fetch_data_new()
	{
		return $this->data_new;
	}
	/**
        * ...
        *
        */
	function fetch_data_old_class()
	{
		if ($this->data_old == $this->data_new)
		{
			return 'unchanged';
		}
		else if ($this->data_old AND empty($this->data_new))
		{
			return 'deleted';
		}
		else if (trim($this->data_old) === '')
		{
			return 'notext';
		}
		else
		{
			return 'changed';
		}
	}
	/**
        * ...
        *
        */
	function fetch_data_new_class()
	{
		if ($this->data_old == $this->data_new)
		{
			return 'unchanged';
		}
		else if ($this->data_new AND empty($this->data_old))
		{
			return 'added';
		}
		else if (trim($this->data_new) === '')
		{
			return 'notext';
		}
		else
		{
			return 'changed';
		}
	}
	/**
        * ...
        *
        */
	function prep_diff_text($string, $wrap = true)
	{
		if (trim($string) === '')
		{
			return '&nbsp;';
		}
		else
		{
			if ($wrap)
			{
				$string = nl2br(htmlspecialchars_uni($string));
				$string = preg_replace('#( ){2}#', '&nbsp; ', $string);
				$string = str_replace("\t", '&nbsp; &nbsp; ', $string);
				return "<code>$string</code>";
			}
			else
			{
				return '<pre style="display:inline">' . "\n" . htmlspecialchars_uni($string) . '</pre>';
			}
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>