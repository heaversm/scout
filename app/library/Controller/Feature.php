<?php
class Controller_Feature extends BaseController_Web {

    protected function editAction() {
        $this->requiresAuth();
        $this->path->setPath(array('feature_id'))->getPathValues();
        $feature_id = $this->memory->feature_id;
        parent::getPage($this->template);
        $response = array();
        $title = $feature_id == 1 ? 'Edit Reel' : 'Edit Montage';
        $this->template->title = $title;
        if($this->isPost()) {
            $title = $this->getRequest('title', '', 'STR');
            $embed_code = $this->getRequest('embed_code', '', 'STR');
            Feature::getInstance()->edit($feature_id, $title, $embed_code);
            $redirect = $feature_id == 1 ? '' : 'montage';
            Helper_Request::respond('/' . $redirect);
        }
        $this->template->form_title = $title;
        $this->template->form_action = '/feature/edit/' . $feature_id;
        $this->template->form_button = 'Save';
        $this->template->form = FormModel::getInstance()
            ->setResponse($response)
            ->setParams(Feature::COLLECTION_NAME, $feature_id, array('title', 'embed_code'))
            ->createForm();
        $this->template->center = array('feature-form');
        $this->setResponse($this->template);
    }
}