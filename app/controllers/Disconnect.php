<?php
class Disconnect extends Controller {

	function _renderDisconnect() {
		$this->disconnect();
		$this->redirect();
	}

}