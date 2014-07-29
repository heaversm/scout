<?php

class Controller_Homepage extends BaseController_Web {

    protected function defaultAction() {
        $this->requiresAuth();
        parent::getPage($this->template);
        $homepages = Homepage::getInstance()->getHomepages();
        $this->template->title = 'Manage Homepage Images';
        $this->template->homepage_table_body = Template::getInstance()->render('homepage-table-row')->iteratorParse($homepages)->getRender();
        $this->template->center = array('homepage-manage');
        $this->setResponse($this->template);
    }

    protected function createAction() {
        $this->requiresAuth();
        parent::getPage($this->template);
        $response = array();
        $this->template->title = 'Create Homepage';
        if($this->isPost()) {
            Homepage::getInstance()->create($_FILES['large_fileid']);
            Helper_Request::respond('/homepage/manage');
        }
        $this->template->form_title = 'Create Homepage Image';
        $this->template->form_action = '/homepage/create';
        $this->template->form_button = 'Create';
        $this->template->form = FormModel::getInstance()
            ->setResponse($response)
            ->setParams(Homepage::COLLECTION_NAME, 0, array('large_fileid'))
            ->createForm();
        $this->template->center = array('homepage-form');
        $this->setResponse($this->template);
    }

    protected function removeAction() {
        $this->requiresAuth();
        $this->path->setPath(array('homepage_id'))->getPathValues();
        $homepage_id = $this->memory->homepage_id;
        if($homepage_id > 0) {
            Homepage::getInstance()->remove($homepage_id);
        }
        Helper_Request::respond('/homepage/manage');
    }
}
