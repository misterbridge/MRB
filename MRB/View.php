<?php

class View {
    protected $layout = '';
    protected $vars = array();

    /**
     * Contructor fo all app views
     * @param   string $layout_name Layout of the page
     * @param   array  $vars        Variables used on the page
     * @return  object              This view
     */ 
    public function __construct($layout_name = 'classic', $vars = array()) {
        if(!empty($layout_name)) {
            $this->layout = APP_PATH . 'views/layouts/' . $layout_name . '.phtml';
        }
        if (is_array($vars) &&  !empty($vars)) {
            $this->vars = $vars;
        }
        return $this;
    }

    /**
     * Set the template
     * @param  string $template_name Template name
     * @return object                This view
     */
    public function template($templat_name) {
        $this->vars['templateName'] = $templat_name;
        $this->vars['template'] = APP_PATH . 'views/' . $templat_name . '.phtml';
        return $this;
    }

    /**
     * Add a menu to the view
     * @param  string $menu_name Name of the menu
     * @return object            This view
     */
    public function addMenu($menu_name = 'classic') {
        $this->vars['menu'] = APP_PATH . 'views/menus/' . $menu_name . '.phtml';
        return $this;
    }

    /**
     * @see MRB/Controller.php
     */
    public function isConnected() {
        return Controller::isConnected();
    }

    /**
     * @see MRB/Controller.php
     */
    public function getRoot() {
        return Controller::getRoot();
    }

    /**
     * @see MRB/Controller.php
     */
    public function getUrl($controller = null, $action = null, $params = null) {
        return Controller::getUrl($controller, $action, $params);
    }

    /**
     * Get the name of the website
     * @return string Name of the site
     */
    public function getSiteName() {
        return Registry::getInstance()->getConf('site/name');
    }

    /**
     * Get the favicon name
     * @return string Name of the favicon
     */
    public function getSiteFavicon() {
        return Registry::getInstance()->getConf('site/favicon');
    }
    
    /**
     * Get the name of the page (template)
     * @return string Name of the template currently used
     */
    public function getCurrentPage() {
        if(!empty($this->vars['templateName'])) {
            return $this->vars['templateName'];
        } else {
            return '';
        }
    }
    
    /**
     * Compare a string to the current page name
     * @param  string  $page String to compare
     * @return boolean       True if the names are the same
     */
    public function isCurrentPage($page) {
        return strcasecmp($this->getCurrentPage(), $page) === 0;
    }

    /**
     * Used to add easily vars to the view
     * @param string $key Identifier of the value
     * @param mixed  $var Value added
     */
    public function __set($key, $var) {
        return $this->set($key,$var);
    }

    /**
     * Set the protected vars
     * @see self::__set
     */
    private function set($key, $var) {
        $this->vars[$key] = $var;
        return $this;
    }

    /**
     * Add some content into a vars' array
     * @param string $key Name of the array
     * @param mixed  $var Value added
     */
    public function add($key,$var) {
        $this->vars[$key][] = $var;
    }

    /**
     * Get a complete content of the view without showing it
     * @param  string $vars Variable to add to the view
     * @return string       Content of the view
     */
    public function fetch($vars = '') {
        if (is_array($vars)) {
            $this->vars = array_merge($this->vars,$vars);
        }
        extract($this->vars);
        ob_start();
        require($this->layout);
        return ob_get_clean();
    }

    /**
     * Print the page
     * @param  string $vars Variables to add to the view
     */
    public function render($vars = '') {
        $this->dump($vars);
    }

    /**
     * Print the page
     * @param  string $vars Variables to add to the view
     */
    private function dump($vars = '') {
        if (is_array($vars)) {
            $this->vars = array_merge($this->vars,$vars);
        }
        extract($this->vars);
        require($this->layout);
    }

    /**
     * Same as fetch except that every params is needed (static View)
     * @param  string $file Layout name
     * @param  string $vars Variables to add to the view
     * @return string       Content of the view
     */
    static function do_fetch($file = '', $vars = '') {
        if (is_array($vars)) {
            extract($vars);
        }
        ob_start();
        require($file);
        return ob_get_clean();
    }

    /**
     * Same as dump except that every params is needed (static View)
     * @param  string $file Layout name
     * @param  string $vars Variables to add to the view
     */
    static function do_dump($file = '', $vars = '') {
        if (is_array($vars)) {
            extract($vars);
        }
        require($file);
    }

    // static function do_fetch_str($str, $vars = '') {
    //     if (is_array($vars)) {
    //         extract($vars);
    //     }
    //     ob_start();
    //     Remove the space
    //     eval('? >' . $str);
    //     return ob_get_clean();
    // }

    // static function do_dump_str($str, $vars = '') {
    //     if (is_array($vars)) {
    //         extract($vars);
    //     }
    //     Remove the space
    //     eval('? >' . $str);
    // }
}