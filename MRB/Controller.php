<?php

class Controller {

    function __construct() {
        if(!$this->isConnected()) {
            $this->disconnect();
            $this->redirect();
        }
    }

    static public function isConnected($set = false) {
        $return = false;
        if($set || (isset($_SESSION['saved']) && $_SESSION['saved']['pass'] === Registry::getInstance()->getConf('sessionPass') && $_SESSION['saved']['TTL'] - time() > 0)) {
            $_SESSION['saved']['pass'] = Registry::getInstance()->getConf('sessionPass');
            $_SESSION['saved']['TTL'] = time() + 60*20; // Plus de 20 minutes sans rafraichir
            $return = true;
        } else {
            $_SESSION['saved']['pass'] = "noAccess";
            $_SESSION['saved']['TTL'] = time() - 1;
        }
        return $return;
    }

    protected function disconnect() {
        foreach($_SESSION as $key => $data) {
            unset($_SESSION[$key]);
        }
    }

    protected function redirect($controller = null) {
        if(isset($controller) && !$this->loopRedirect($this->getRoot().$controller)) {
            header('Location:' . $this->getRoot().$controller);
            exit();
        }
        elseif(!$this->loopRedirect($this->getRoot())) {
            header('Location:' . $this->getRoot());
            exit();
        }
    }

    private function loopRedirect($url) {
        return $_SERVER['REQUEST_URI'] == $url;
    }

    static public function getRoot() {;
        return BASE_PATH;
    }

    static public function getUrl($controller = null, $action = null, $params = null) {
        if (isset($params) && is_array($params)) {
            return BASE_PATH.$controller.'/'.$action.'/'.implode('/', $params);
        }
        if (isset($action)) {
            return BASE_PATH.$controller.'/'.$action;
        }
        elseif (isset($controller)) {
            return BASE_PATH.$controller;
        }
        else {
            return BASE_PATH;
        }
    }

}
