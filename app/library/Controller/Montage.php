<?php
class Controller_Montage extends BaseController_Web {

    protected function defaultAction() {
        parent::getPage($this->template, 'montage');

        $feature = Feature::getInstance()->getText(2);
        $this->template->title = 'Montage';
        $this->template->feature_title = $feature['title'];
        $this->template->embed_code = $feature['embed_code'];
        $this->template->center = array('montage');
        $this->setResponse($this->template);
    }
}