<?php

/**
 * Config
 * Manage the application configuration
 *
 * @package MRB
 * @author Sylvain Pont <MrBridge.framework@gmail.com>
 */

class Config {

	private $conf = array();

	public function __construct() {
		$this->conf = require 'config\config.php';
	}
	private function __clone() {}

	/**
	 * Get the value of searched config key
	 * @param  string $key The key of the searched entry or a chain separed by slashes for deep entries
	 * @return string      Entry value
	 */
	public function get($key) {
		$keys = explode('/', $key);
		$value = &$this->conf;

		foreach ($keys as $k) {
			if (!isset($value[$k])) {
				throw new \Exception("There is no entry for key " . $key);
			}
			$value = &$value[$k];
		}

		return $value;
	}

}