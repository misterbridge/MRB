<?php
class Login extends Controller {

	function _renderLogin() {
		
		$registry = Registry::getInstance();
		$passwords = $registry->getConf('passwords');

		$badLogin = false;
		if(!empty($_REQUEST['pass']) && (in_array($_REQUEST['pass'], $passwords))) {
			$this->isConnected(true);
		}
		else if(!empty($_REQUEST['pass'])) {
			$badLogin = true;
		}

		if($this->isConnected()) {
			$this->redirect('home');
		} else {
			$view = new View();
			$view->template('login');

			$view->errorLog = $badLogin;
			$view->specialClass = 'height180';
			$view->render();
		}

	}

}