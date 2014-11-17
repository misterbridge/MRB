<?php
class EventModel extends Model {
    
    function __construct($title = null) {
        parent::__construct('calendar');
        
        if(!empty($title)) {
            $this->setTitle($title);
        }
        return $this;
    }
    
    public function setTitle($title) {
        $this->set('title', $title);
    }
    
    public function setDates($start, $end = null) {
        $this->setBegins($start);
        if($end !== null) {
            $this->setEnds($end);
        }
    }
    
    public function setBegins($start) {
        $this->date_start = strtotime($start);
    }
    
    public function setEnds($end) {
        $this->date_end = strtotime($end);
    }
    
    public function setContent($content) {
        $this->content = $content;
    }
    
    public function setLocation($location) {
        $this->location = $location;
    }
        
}