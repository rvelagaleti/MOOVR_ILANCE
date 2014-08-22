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
 * ILance modules configuration class
 *
 * @package      iLance\Modules
 * @version	 4.0.0.8059
 * @author       ILance
 */
class modules
{
	public $module;
	public $config;
	function __construct(){}
	public function init_module_configuration($module)
	{
		global $ilance;
		$ilance->timer->start();
		$this->module = $module;
		$this->{$module} = new stdClass();
		if (!isset($this->config[$this->module]))
		{
			$query = $ilance->db->query("
				SELECT configtable, version
				FROM " . DB_PREFIX . "modules_group
				WHERE modulegroup = '" . $ilance->db->escape_string($this->module) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				$table = $ilance->db->fetch_array($query, DB_ASSOC);
				if (!empty($table['configtable']))
				{
					$sql = $ilance->db->query("SELECT name, value FROM " . DB_PREFIX . $table['configtable'], 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$this->{$module}->config[$res['name']] = $res['value'];
						}
						unset($res);
						$this->{$module}->config['version'] = $table['version'];
					}
					unset($sql);
				}
				unset($table);
			}
			unset($query);
			
			($apihook = $ilance->api('maincp_leftnav_load_module_for_external')) ? eval($apihook) : false;
		}
		$ilance->timer->stop();
		DEBUG("init_module_configuration(\$module = $this->module) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}
	function __isset($name)
	{
		$this->init_module_configuration($name);
		return $this->{$name};
	}
	function __get($name)
	{
		$this->init_module_configuration($name);
		return $this->{$name};
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>