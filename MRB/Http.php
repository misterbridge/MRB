<?php
/**
 * MRB_Controller
 * Base Controller
 *
 * @package MRB
 * @author  Sylvain Pont <MrBridge.framework@gmail.com>
 */

class Http {

    /**
     * Folder containings all controllers within APP_PATH
     * @var string
     */
    protected $controllersFolder = 'controllers';
    
    /**
     * Path to root
     * @var string
     */
    protected $root = BASE_PATH;

    /**
     * Array containing all parts of the URI
     * @var array
     */
    protected $mvcUri = array();

    /**
     * Store current controller called
     * Default controller used if none given
     * @var string
     */
    protected $controller = 'Login';

    /**
     * Store current action called
     * @var string
     */
    protected $action = null;

    /**
     * Array containing additional params contained on the URI
     * @var array
     */
    protected $params = array();

    /**
     * Redirect to the called URI
     */
    public function __construct() {}

    public function startMvc() {
        set_exception_handler(array($this, 'exceptionUncaught'));
        set_error_handler(array($this, 'errorUncaught'));
        $this->getHttpRequest()->getRequestMVC()->routeRequest();
    }

    /**
     * Get MVC part of the URI
     * @return MRB_Controller
     */
    protected function getHttpRequest() {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $this->root) === 0) {
            $uri = substr($uri, strlen($this->root));
        }
        $this->mvcUri = $uri ? explode('/', $uri) : array();
        return $this;
    }

    /**
     * Parse URI and get from it MVC path and params
     * @return MRB_Controller
     */
    protected function getRequestMVC() {
        $this->params = array();
        if(isset($this->mvcUri[0]) && $this->mvcUri[0]) {
            if($this->mvcUri[0][0] != '?') {
                $this->controller = ucfirst($this->mvcUri[0]);
            } else {
                $this->params = $this->mvcUri;
            }
        }
        if(isset($this->mvcUri[1]) && $this->mvcUri[1]) {
            if($this->mvcUri[1][0] != '?') {
                $this->action = $this->mvcUri[1];
            } else if(empty($this->params)) {
                $this->params = array_slice($this->mvcUri, 1);
            }
        }
        if(isset($this->mvcUri[2]) && empty($this->params)) {
            $this->params = array_slice($this->mvcUri, 2);
        }
        return $this;
    }

    /**
     * Maps the MVC path to the location of the PHP file to include
     */
    protected function routeRequest() {
        $controllerfile = APP_PATH.$this->controllersFolder.'/'.$this->controller.'.php';
        if (!preg_match('#^[A-Za-z0-9_-]+$#', $this->controller) || !file_exists($controllerfile))
            $this->fileNotFound('File not found: ' . $controllerfile);
        require_once($controllerfile);
        if (!class_exists($this->controller))
            $this->fileNotFound('Invalid controller name: ' . $this->controller);
        $controller = new $this->controller;
        if ($this->action === null) {
            $this->action = '_render'.$this->controller;
        }
        if (!preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#', $this->action) || !method_exists($controller, $this->action))
            $this->request_not_found('Invalid function name: ' . $this->controller . ' / ' . $this->action);
        call_user_func_array(array($controller, $this->action), $this->params);
    }

    /**
     * Standard file for 404 error
     * @param  string $msg
     *      Contain message to display on 404 screen
     * @return die
     */
    public function fileNotFound($msg = '') {
        header("HTTP/1.0 404 Not Found");
        die(require('MRB\error\404.phtml'));
    }

    /**
     * Standard file for 404 error
     * @param  string $msg
     *      Contain message to display on 404 screen
     * @return die
     */
    public function request_not_found($msg = '') {
        header("HTTP/1.0 404 Not Found");
        die(require('MRB\error\404.phtml'));
    }

    /**
     * Standard file to catch uncaught exceptions
     * @param  Exception $e
     * @return die
     */
    public function exceptionUncaught($e) {
        ob_end_clean();
        header("HTTP/1.0 404 Not Found");
        die(require('MRB\error\Exception.phtml'));
    }

    /**
     * Standard file to catch uncaught exceptions
     * @param  Exception $e
     * @return die
     */
    public function errorUncaught($errno, $errstr, $errfile, $errline, $errcontext) {
        ob_end_clean();
        header("HTTP/1.0 404 Not Found");
        die(require('MRB\error\Error.phtml'));
    }
}