<?php

class Controller_Reel extends BaseController_Web {

    protected function defaultAction() {
        parent::getPage($this->template, 'home');

        $homepage = Homepage::getInstance()->getHomepage();
        $this->template->title = 'Homepage';
        $this->template->image = $homepage['asset_image'];
        $this->template->center = array('reel');
        $this->setResponse($this->template);

    }
}
