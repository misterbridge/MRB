<?php
class Home extends Controller {

    function _renderHome() {
        $view = new View();
        $view->template('home');
        $view->addMenu();

        $view->render();
    }

}