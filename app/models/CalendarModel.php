<?php
class CalendarModel extends Model {
    
    protected $events = array();
    
    public function createEvent() {
        $event = new EventModel('ttt');
        $event->setContent('');
        $event->setDates('');
        $event->setLocation('');
        return $event;
    }
    
    public function getCalendarForMonth($month) {
        
    }
    
}