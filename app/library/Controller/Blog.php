<?php
class Controller_Blog extends BaseController_Web {

    protected function defaultAction() {
        parent::getPage($this->template, 'blog');

        $this->template->title = 'Blog';
        $this->template->center = array('blog');
        $this->setResponse($this->template);
    }
}
