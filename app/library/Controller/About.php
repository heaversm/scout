<?php
class Controller_About extends BaseController_Web {

    protected function defaultAction() {
        parent::getPage($this->template, 'about');

        $this->template->title = 'About';
        $this->template->center = array('about');
        $this->setResponse($this->template);
    }
}