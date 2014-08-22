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
* XML RPC server class to perform the majority of xml remote call procedures tasks in iLance.
*
* @package      iLance\XMLRPC
* @version      4.0.0.8059
* @author       ILance
*/
class xmlrpcserver
{
	private $server_handler;
	private $external_functions;
	public function __construct()
	{
		$this->server_handler = xmlrpc_server_create();
		$this->external_functions = array();
	}
	
	public function register_method($external_name, $function, $parameter_names)
	{
		if ($function == null)
		{
			$function = $external_name;
		}
		xmlrpc_server_register_method($this->server_handler, $external_name, array(&$this, 'call_method'));
		$this->external_functions[$external_name] = array('function' => $function, 'parameter_names' => $parameter_names);
	}
	
	public function call_method($function_name, $parameters_from_request)
	{
		$function = $this->external_functions[$function_name]['function'];
		$parameter_names = $this->external_functions[$function_name]['parameter_names'];
		$parameters = array();
		foreach ($parameter_names AS $parameter_name)
		{
			$parameters[] = $parameters_from_request[0][$parameter_name];
		}
		return call_user_func_array($function, $parameters);
	}
	
	public function send_reponse()
	{
		return xmlrpc_server_call_method($this->server_handler, file_get_contents('php://input'), null);
	}
}
/**
* XML RPC server class for iLance
*
* @package      iLance\XMLRPC\Server
* @version      4.0.0.8059
* @author       ILance
*/
class ilance_xmlrpcserver
{
	private $xmlrpcserver;
	public function __construct($xmlrpcserver)
	{
		$this->xmlrpcserver = $xmlrpcserver;
		$this->xmlrpcserver->register_method(
			'fetch_user_id', array(&$this, 'fetch_user_id'), array('username')
		);
	}
	
	public function fetch_user_id($username)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "users
			WHERE username = '" . $ilance->db->escape_string($username) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $userinfo['user_id'];
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>