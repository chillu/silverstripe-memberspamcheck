<?php
/**
 * @package memberspamcheck
 */

/**
 * Base implementation of a service to evaluate members against certain spam criteria.
 * Could inspect the member record based on internal criteria, e.g. the number of posts with less than
 * one sentence over a period of time. Or query external blacklisting services, 
 * see {@link MemberSpamCheckService_StopForumSpamOrg}.
 */
abstract class MemberSpamCheckService {
	
	/**
	 * @var array Map of field identifiers used by the services, to actual database fields on a {@link Member} object.
	 * Leave fields empty to ignore them in services.
	 * Overwrite them if the fields are different in your own member implementation.
	 * Caution: Some services might not work without at least these three fields.
	 */
	static $default_property_map = array(
		'Nickname' => 'Nickname', // present in forum
		'Email' => 'Email',
		'IP' => null, // ignored by default
	);
	
	/**
	 * @var array See {@link $default_property_map}
	 */
	protected $propertyMap;
	
	/**
	 * @param array
	 */
	function setPropertyMap($map) {
		$this->propertyMap = $map;
	}
	
	/**
	 * @return array
	 */
	function getPropertyMap() {
		if(!$this->propertyMap) $this->propertyMap = self::$default_property_map;
		return $this->propertyMap;
	}
	
	/**
	 * Note: This might trigger a lot of HTTP calls, so use sparingly.
	 * 
	 * @param DataObjectSet Set of {@link Member} objects
	 * 
	 * @return Array Map of IDs
	 */
	abstract function update($members);
	
	/**
	 * @todo Should use SS_Log::log(), but that class is seriously messed up...
	 */
	protected function output($msg) {
		if(!SapphireTest::is_running_test()) echo $msg . "\n";
	}
}