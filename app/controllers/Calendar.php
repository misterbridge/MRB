<?php
class Calendar extends Controller {

    function _renderCalendar() {
        $calendar = new CalendarModel();
        $event = $calendar->createEvent();
        
        $view = new View();
        $view->template('calendar');
        $view->addMenu();

        $view->render();
    }

}