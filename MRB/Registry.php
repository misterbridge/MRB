<?php

/**
 * Registry
 * Manage the application registry
 *
 * @package MRB
 * @author Sylvain Pont <MrBridge.framework@gmail.com>
 */

class Registry {
    /**
     * Registry vars
     * @var array
     */
    private $registry = array();
    /**
     * Instance of this class (STATIC)
     * @var Registry
     */
    private static $instance = null;

    /**
     * Constructor of the registry : Instanciate the Config()
     */
    private function __construct() {
        if(!$this->exists('config')) {
            $this->set('config', new Config());
        }
    }
    private function __clone() {}

    /**
     * Get the registry
     * @return Object The current registry
     */
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new Registry();
        }

        return self::$instance;
    }

    /**
     * Set a value paired to a key
     * @param String $key   Key of the value in the registry
     * @param mixed  $value Value to be saved on the registry
     */
    public function set($key, $value) {
        if (isset($this->registry[$key])) {
            throw new \Exception("There is already an entry for key " . $key);
        }

        $this->registry[$key] = $value;
    }

    /**
     * Get a value from its key
     * @param  String $key Key of the value to be retrieved
     * @return mixed       The wanted value
     * @example
     *      If you try to get 'user/first' you'll get $registry['user']['first']
     */
    public function get($key) {
        $keys = explode('/', $key);
        $value = &$this->registry;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                throw new \Exception("There is no entry for key " . $key);
            }
            $value = &$value[$key];
        }


        return $value;
    }

    /**
     * Verify the existance of a key in the registry
     * @param  String $key Key of the value to be checked
     * @return boolean     Result of the existance of this key
     */
    public function exists($key) {
        return isset($this->registry[$key]);
    }

    /**
     * Get directly the configuration entry from the registry
     *  -> Accepts parameters to pass to the get() method of the Config class
     * @param  String || null $key Key of the config
     * @return mixed          The config or the resutl of the key in the config
     */
    public function getConf($key = null) {
        if(isset($key)) {
            return $this->get('config')->get($key);
        } else {
            return $this->get('config');
        }
    }
}