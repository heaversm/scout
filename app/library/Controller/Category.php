<?php
class Controller_Category extends BaseController_Web {

	protected function defaultAction() {
		$this->requiresAuth();
		parent::getPage($this->template);
		$categories = Category::getInstance()->getCategories();
		$this->template->title = 'Manage Categories';
		$this->template->category_table_body = Template::getInstance()->render('category-table-row')->iteratorParse($categories)->getRender();
		$this->template->center = array('category-manage');
		$this->setResponse($this->template);
	}
	
	protected function manageAction() {
		$this->defaultAction();
	}
	
	protected function saveOrderAction() {
		$this->requiresAuth();
		$order = $this->getRequest('order', array(), 'ARR');
		Category::getInstance()->saveOrder($order);
		return $this;
	}
	
	protected function createAction() {
		$this->requiresAuth();
		parent::getPage($this->template);
		$response = array();
		$this->template->title = 'Create Category';
		if($this->isPost()) {
			$name = $this->getRequest('name', '', 'STR');
			Category::getInstance()->create($name);
			Helper_Request::respond('/category/manage');
		}
		$this->template->form_title = 'Create Category';
		$this->template->form_action = '/category/create';
		$this->template->form_button = 'Create';
		$this->template->form = FormModel::getInstance()
			->setResponse($response)
			->setParams(Category::COLLECTION_NAME, 0, array('name'))
			->createForm();
		$this->template->center = array('category-form');
		$this->setResponse($this->template);
	}
	
	protected function editAction() {
		$this->requiresAuth();
		parent::getPage($this->template);
		$response = array();
		$this->template->title = 'Edit Category';
		$this->path->setPath(array('category_id'))->getPathValues();
		$category_id = $this->memory->category_id;
		if($this->isPost()) {
			$category_id = $this->getRequest('id', '', 'STR');
			$name = $this->getRequest('name', '', 'STR');
			Category::getInstance()->edit($category_id, $name);
			Helper_Request::respond('/category/manage');
		}
		$this->template->form_title = 'Edit Category';
		$this->template->form_action = '/category/edit';
		$this->template->form_button = 'Save';
		$this->template->form = FormModel::getInstance()
			->setResponse($response)
			->setParams(Category::COLLECTION_NAME, $category_id, array('name'))
			->createForm();
		$this->template->center = array('category-form');
		$this->setResponse($this->template);
	}
	
	protected function removeAction() {
		$this->requiresAuth();
		$this->path->setPath(array('category_id'))->getPathValues();
		$category_id = $this->memory->category_id;
		if($category_id > 0) {
			Category::getInstance()->remove($category_id);
		}
		Helper_Request::respond('/category/manage');
	}
}