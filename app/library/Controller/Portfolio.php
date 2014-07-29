<?php
class Controller_Portfolio extends BaseController_Web {

	protected function defaultAction() {
        $this->path->setPath(array('project_id'))->getPathValues();
        $project_id = $this->memory->project_id;
        if($project_id > 0) {
            $project = Portfolio_Project::getInstance();
            $project = $project->getProjectInfo($project_id);
            $category_id = $project['category_id'];
            $active = ($category_id == 2) ? 'almost' : 'work-project';
            $featured_active = ($category_id == 2) ? '' : 'active';
            $almost_active = ($category_id == 2) ? 'active' : '';
        } else {
            $active = 'work';
            $featured_active = 'active';
            $almost_active = '';
        }
		parent::getPage($this->template, $active);
        $this->template->submenu = Template::getInstance()->render('submenu')->iteratorParse(array(array('featured' => $featured_active, 'almost' => $almost_active)))->getRender();
		$this->template->has_submenu_li = 'has-drop-down';
		$this->template->has_submenu_a = 'has-drop-down-a';
        if($project_id > 0) {
            $this->template->title = $project['name'];
            $this->template->name = $project['name'];
            $this->template->client_name = $project['client_name'];
            $this->template->description = $project['description'];
            if (Helper_Browser::isIphone()) {
                $embed_width = 300;
                $embed_height = 169;
            } elseif (Helper_browser::isIpad()) {
                $embed_width = 960;
                $embed_height = 540;
            } else {
                $embed_width = 960;
                $embed_height = 540;
            }

            $this->template->hero = $project['hero'];
            $this->template->vimeo_url = $project['vimeo_url'];
            $video_embed1 = Helper_Publish::resizeEmbedCode($project['video_embed1'], $embed_width, $embed_height);
            $video_embed2 = Helper_Publish::resizeEmbedCode($project['video_embed2'], $embed_width, $embed_height);
            $video_embed3 = Helper_Publish::resizeEmbedCode($project['video_embed3'], $embed_width, $embed_height);
            $video_embed4 = Helper_Publish::resizeEmbedCode($project['video_embed4'], $embed_width, $embed_height);
            $video_embed5 = Helper_Publish::resizeEmbedCode($project['video_embed5'], $embed_width, $embed_height);
            $video_embed6 = Helper_Publish::resizeEmbedCode($project['video_embed6'], $embed_width, $embed_height);
            $video_embed7 = Helper_Publish::resizeEmbedCode($project['video_embed7'], $embed_width, $embed_height);
            $video_embed8 = Helper_Publish::resizeEmbedCode($project['video_embed8'], $embed_width, $embed_height);
            $video_embed9 = Helper_Publish::resizeEmbedCode($project['video_embed9'], $embed_width, $embed_height);
            $video_embed10 = Helper_Publish::resizeEmbedCode($project['video_embed10'], $embed_width, $embed_height);
            $this->template->hero = ($project['hero'] != '' && $project['hero'] != -2) ? '<img src="' . Config::$assets_url.$project['hero'] . '"/>' : '';
            // $this->template->slide = ($project['hero'] != '' && $project['hero'] != -2) ? '<img src="' . Config::$assets_url.$project['hero'] . '"/>' : $video_embed1.$video_embed2.$video_embed3.$video_embed4.$video_embed5.$video_embed6.$video_embed7.$video_embed8.$video_embed9.$video_embed10;
            $this->template->slide = $video_embed1.$video_embed2.$video_embed3.$video_embed4.$video_embed5.$video_embed6.$video_embed7.$video_embed8.$video_embed9.$video_embed10;
            $this->template->next_project = $project['next_project'];
            $this->template->previous_project = $project['previous_project'];
            $still_images = Portfolio_Asset::getInstance()->getAssets($project_id, 'still');
            $process_images = Portfolio_Asset::getInstance()->getAssets($project_id, 'process');
            $credits = Portfolio_Credit::getInstance()->getCredits($project_id);
            $this->template->hide_images_01 = count($still_images) > 0 ? '' : 'hide';
            $this->template->hide_images_02 = count($process_images) > 0 ? '' : 'hide';
            $this->template->still_images = Template::getInstance()->render('asset-images')->iteratorParse($still_images)->getRender();
            $this->template->process_images = Template::getInstance()->render('asset-images')->iteratorParse($process_images)->getRender();
            $this->template->credits = Template::getInstance()->render('credits')->iteratorParse($credits)->getRender();
            $template_file = ($category_id == 2) ? 'project-almost-item' : 'project-item';
			$this->template->center = array($template_file);
		} else {
			$this->template->title = 'Home';
			$projects = Portfolio_Project::getInstance()->getProjects(1, true);
			$this->template->projects = Template::getInstance()->render('work-project-item')->iteratorParse($projects)->getRender();
			$this->template->center = array('work');
		}
		$this->setResponse($this->template);
	}

    protected function almostAction() {
        parent::getPage($this->template);
        $this->template->has_submenu_li = 'has-drop-down';
        $this->template->has_submenu_a = 'has-drop-down-a';
        $this->path->setPath(array('project_id'))->getPathValues();
        $project_id = $this->memory->project_id;
        $this->template->submenu = Template::getInstance()->render('submenu')->iteratorParse(array(array('featured' => '', 'almost' => 'active')))->getRender();
        $this->template->title = 'Home';
        $projects = Portfolio_Project::getInstance()->getProjects(2, true);
        $this->template->projects = Template::getInstance()->render('work-project-item')->iteratorParse($projects)->getRender();
        $this->template->center = array('work');
        $this->setResponse($this->template);
    }

	protected function byCategoryAction() {
		parent::getPage($this->template);
		$this->path->setPath(array('category_id'))->getPathValues();
		$this->template->addFlag('category_slug', $this->memory->category_id);
		$this->template->title = Category::getInstance()->getName($this->memory->category_id);
		$projects = Portfolio_Project::getInstance()->getProjects(true);
		$this->template->projects = Template::getInstance()->render('home-page-project-item')->iteratorParse($projects)->getRender();
		$this->template->center = array('home-page');
		$this->setResponse($this->template);
	}

	protected function almostManageAction() {
		$this->requiresAuth();
		parent::getPage($this->template);
		$projects = Portfolio_Project::getInstance()->getProjects(2);
		$this->template->title = 'Manage Almost';
		$this->template->type = 'Almost';
		$this->template->portfolio_table_body = Template::getInstance()->render('portfolio-table-row')->iteratorParse($projects)->getRender();
		$this->template->center = array('portfolio-almost-manage');
		$this->setResponse($this->template);
	}

    protected function manageAction() {
        $this->requiresAuth();
        parent::getPage($this->template);
        $projects = Portfolio_Project::getInstance()->getProjects(1);
        $this->template->title = 'Manage Featured';
        $this->template->type = 'Featured';
        $this->template->portfolio_table_body = Template::getInstance()->render('portfolio-table-row')->iteratorParse($projects)->getRender();
        $this->template->center = array('portfolio-manage');
        $this->setResponse($this->template);
    }

	protected function saveOrderAction() {
		$this->requiresAuth();
		$order = $this->getRequest('order', array(), 'ARR');
		Portfolio_Project::getInstance()->saveOrder($order);
		return $this;
	}

	protected function saveAssetOrderAction() {
		$this->requiresAuth();
		$order = $this->getRequest('order', array(), 'ARR');
		Portfolio_Asset::getInstance()->saveOrder($order);
		return $this;
	}

    protected function saveCreditOrderAction() {
        $this->requiresAuth();
        $order = $this->getRequest('order', array(), 'ARR');
        Portfolio_Credit::getInstance()->saveOrder($order);
        return $this;
    }

	protected function createAction() {
		$this->requiresAuth();
		parent::getPage($this->template);
		$response = array();
		$this->template->title = 'Create Featured Project';
		if($this->isPost()) {
			$name = $this->getRequest('name', '', 'STR');
            $client_name = $this->getRequest('client_name', '', 'STR');
            $video_embed1 = $this->getRequest('video_embed1', '', 'STR');
            $video_embed2 = $this->getRequest('video_embed2', '', 'STR');
            $video_embed3 = $this->getRequest('video_embed3', '', 'STR');
            $video_embed4 = $this->getRequest('video_embed4', '', 'STR');
            $video_embed5 = $this->getRequest('video_embed5', '', 'STR');
            $video_embed6 = $this->getRequest('video_embed6', '', 'STR');
            $video_embed7 = $this->getRequest('video_embed7', '', 'STR');
            $video_embed8 = $this->getRequest('video_embed8', '', 'STR');
            $video_embed9 = $this->getRequest('video_embed9', '', 'STR');
            $video_embed10 = $this->getRequest('video_embed10', '', 'STR');
			$description = $this->getRequest('description', '', 'STR');
            $vimeo_url = $this->getRequest('vimeo_url', '', 'STR');
            $credits = $this->getRequest('credit', array(), 'ARR');
			Portfolio_Project::getInstance()->create($name, $client_name, 1, $_FILES['cover_fileid'], $_FILES['hero_fileid'], $credits, $_FILES['still'], $_FILES['process'], $video_embed1, $video_embed2, $video_embed3, $video_embed4, $video_embed5, $video_embed6, $video_embed7, $video_embed8, $video_embed9, $video_embed10, $description, $vimeo_url);
			Helper_Request::respond('/portfolio/manage');
		}
        $credit = Portfolio_Credit::getInstance()->calculateCreditsFields(0);
        $stills = Portfolio_Asset::getInstance()->calculateAssetsFields(0, 'stills');
        $process = Portfolio_Asset::getInstance()->calculateAssetsFields(0, 'process');
        $this->template->new_still_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($stills['new'])->getRender();
        $this->template->new_process_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($process['new'])->getRender();
        $this->template->new_credit_table_row = Template::getInstance()->render('new-credit-table-row')->iteratorParse($credit['new'])->getRender();
        $this->template->current_still_table_row = '';
        $this->template->current_process_table_row = '';
        $this->template->current_credit_table_row = '';
		$this->template->form_title = 'Create Project';
		$this->template->form_action = '/portfolio/create';
		$this->template->form_button = 'Create';
		$this->template->form = FormModel::getInstance()
			->setResponse($response)
			->setParams(Portfolio_Project::COLLECTION_NAME, 0, array('name', 'client_name', 'category_id', 'cover_fileid', 'hero_fileid', 'vimeo_url', 'video_embed1', 'video_embed2', 'video_embed3', 'video_embed4', 'video_embed5', 'video_embed6', 'video_embed7', 'video_embed8', 'video_embed9', 'video_embed10', 'description'))
			->createForm();
		$this->template->center = array('portfolio-form');
        $this->template->remove_hero = '';
		$this->setResponse($this->template);
	}

	protected function editAction() {
		$this->requiresAuth();
		parent::getPage($this->template);
		$response = array();
		$this->template->title = 'Edit Project';
		$this->path->setPath(array('project_id'))->getPathValues();
		$project_id = $this->memory->project_id;
		if($this->isPost()) {
			$project_id = $this->getRequest('id', '', 'STR');
            $name = $this->getRequest('name', '', 'STR');
            $client_name = $this->getRequest('client_name', '', 'STR');
            $video_embed1 = $this->getRequest('video_embed1', '', 'STR');
            $video_embed2 = $this->getRequest('video_embed2', '', 'STR');
            $video_embed3 = $this->getRequest('video_embed3', '', 'STR');
            $video_embed4 = $this->getRequest('video_embed4', '', 'STR');
            $video_embed5 = $this->getRequest('video_embed5', '', 'STR');
            $video_embed6 = $this->getRequest('video_embed6', '', 'STR');
            $video_embed7 = $this->getRequest('video_embed7', '', 'STR');
            $video_embed8 = $this->getRequest('video_embed8', '', 'STR');
            $video_embed9 = $this->getRequest('video_embed9', '', 'STR');
            $video_embed10 = $this->getRequest('video_embed10', '', 'STR');
            $description = $this->getRequest('description', '', 'STR');
            $vimeo_url = $this->getRequest('vimeo_url', '', 'STR');
            $credits = $this->getRequest('credit', array(), 'ARR');
            $current_credits = $this->getRequest('current_credit', array(), 'ARR');
            Portfolio_Project::getInstance()->edit($project_id, $name, $client_name, 1, $_FILES['cover_fileid'], $_FILES['hero_fileid'], $credits, $current_credits, $_FILES['still'], $_FILES['process'], $video_embed1, $video_embed2, $video_embed3, $video_embed4, $video_embed5, $video_embed6, $video_embed7, $video_embed8, $video_embed9, $video_embed10, $description, $vimeo_url);
            Helper_Request::respond('/portfolio/manage');
		}

        $credit = Portfolio_Credit::getInstance()->calculateCreditsFields($project_id);
        $stills = Portfolio_Asset::getInstance()->calculateAssetsFields($project_id, 'still');
        $process = Portfolio_Asset::getInstance()->calculateAssetsFields($project_id, 'process');

        $this->template->new_still_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($stills['new'])->getRender();
        $this->template->current_still_table_row = Template::getInstance()->render('current-asset-table-row')->iteratorParse($stills['current'])->getRender();


        $this->template->new_process_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($process['new'])->getRender();
        $this->template->current_process_table_row = Template::getInstance()->render('current-asset-table-row')->iteratorParse($process['current'])->getRender();

        $this->template->new_credit_table_row = Template::getInstance()->render('new-credit-table-row')->iteratorParse($credit['new'])->getRender();
        $this->template->current_credit_table_row = Template::getInstance()->render('current-credit-table-row')->iteratorParse($credit['current'])->getRender();

		$this->template->form_title = 'Edit Project';
		$this->template->form_action = '/portfolio/edit';
		$this->template->form_button = 'Save';
		$this->template->form = FormModel::getInstance()
			->setResponse($response)
			->setParams(Portfolio_Project::COLLECTION_NAME, $project_id, array('name', 'client_name', 'cover_fileid', 'hero_fileid', 'vimeo_url', 'video_embed1', 'video_embed2', 'video_embed3', 'video_embed4', 'video_embed5', 'video_embed6', 'video_embed7', 'video_embed8', 'video_embed9', 'video_embed10', 'description'))
			->createForm();
		$this->template->center = array('portfolio-form');
        $this->template->remove_hero = '<a href="/portfolio/remove-hero/'.$project_id.'">Remove Hero</a>';
		$this->setResponse($this->template);
	}



    protected function createAlmostAction() {
        $this->requiresAuth();
        parent::getPage($this->template);
        $response = array();
        $this->template->title = 'Create Almost Project';
        if($this->isPost()) {
            $name = $this->getRequest('name', '', 'STR');
            $client_name = $this->getRequest('client_name', '', 'STR');
            $video_embed1 = $this->getRequest('video_embed1', '', 'STR');
            $video_embed2 = $this->getRequest('video_embed2', '', 'STR');
            $video_embed3 = $this->getRequest('video_embed3', '', 'STR');
            $video_embed4 = $this->getRequest('video_embed4', '', 'STR');
            $video_embed5 = $this->getRequest('video_embed5', '', 'STR');
            $video_embed6 = $this->getRequest('video_embed6', '', 'STR');
            $video_embed7 = $this->getRequest('video_embed7', '', 'STR');
            $video_embed8 = $this->getRequest('video_embed8', '', 'STR');
            $video_embed9 = $this->getRequest('video_embed9', '', 'STR');
            $video_embed10 = $this->getRequest('video_embed10', '', 'STR');
            $description = $this->getRequest('description', '', 'STR');
            $credits = $this->getRequest('credit', array(), 'ARR');
            Portfolio_Project::getInstance()->create($name, $client_name, 2, $_FILES['cover_fileid'], $_FILES['hero_fileid'], $credits, $_FILES['still'], $_FILES['process'], $video_embed1, $video_embed2, $video_embed3, $video_embed4, $video_embed5, $video_embed6, $video_embed7, $video_embed8, $video_embed9, $video_embed10, $description, '');
            Helper_Request::respond('/portfolio/almost-manage');
        }
        $credit = Portfolio_Credit::getInstance()->calculateCreditsFields(0);
        $stills = Portfolio_Asset::getInstance()->calculateAssetsFields(0, 'stills');
        $process = Portfolio_Asset::getInstance()->calculateAssetsFields(0, 'process');
        $this->template->new_still_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($stills['new'])->getRender();
        $this->template->new_process_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($process['new'])->getRender();
        $this->template->new_credit_table_row = Template::getInstance()->render('new-credit-table-row')->iteratorParse($credit['new'])->getRender();
        $this->template->current_still_table_row = '';
        $this->template->current_process_table_row = '';
        $this->template->current_credit_table_row = '';
        $this->template->form_title = 'Create Almost';
        $this->template->form_action = '/portfolio/create-almost';
        $this->template->form_button = 'Create';
        $this->template->form = FormModel::getInstance()
            ->setResponse($response)
            ->setParams(Portfolio_Project::COLLECTION_NAME, 0, array('name', 'client_name', 'category_id', 'cover_fileid', 'hero_fileid', 'vimeo_url', 'video_embed1', 'video_embed2', 'video_embed3', 'video_embed4', 'video_embed5', 'video_embed6', 'video_embed7', 'video_embed8', 'video_embed9', 'video_embed10', 'description'))
            ->createForm();
        $this->template->center = array('portfolio-almost-form');
        $this->template->remove_hero = '';
        $this->setResponse($this->template);
    }

    protected function editAlmostAction() {
        $this->requiresAuth();
        parent::getPage($this->template);
        $response = array();
        $this->template->title = 'Edit Project';
        $this->path->setPath(array('project_id'))->getPathValues();
        $project_id = $this->memory->project_id;
        if($this->isPost()) {
            $project_id = $this->getRequest('id', '', 'STR');
            $name = $this->getRequest('name', '', 'STR');
            $client_name = $this->getRequest('client_name', '', 'STR');
            $video_embed1 = $this->getRequest('video_embed1', '', 'STR');
            $video_embed2 = $this->getRequest('video_embed2', '', 'STR');
            $video_embed3 = $this->getRequest('video_embed3', '', 'STR');
            $video_embed4 = $this->getRequest('video_embed4', '', 'STR');
            $video_embed5 = $this->getRequest('video_embed5', '', 'STR');
            $video_embed6 = $this->getRequest('video_embed6', '', 'STR');
            $video_embed7 = $this->getRequest('video_embed7', '', 'STR');
            $video_embed8 = $this->getRequest('video_embed8', '', 'STR');
            $video_embed9 = $this->getRequest('video_embed9', '', 'STR');
            $video_embed10 = $this->getRequest('video_embed10', '', 'STR');
            $description = $this->getRequest('description', '', 'STR');
            $credits = $this->getRequest('credit', array(), 'ARR');
            $current_credits = $this->getRequest('current_credit', array(), 'ARR');
            Portfolio_Project::getInstance()->edit($project_id, $name, $client_name, 2, $_FILES['cover_fileid'], $_FILES['hero_fileid'], $credits, $current_credits, $_FILES['still'], $_FILES['process'], $video_embed1, $video_embed2, $video_embed3, $video_embed4, $video_embed5, $video_embed6, $video_embed7, $video_embed8, $video_embed9, $video_embed10, $description, '');
            Helper_Request::respond('/portfolio/almost-manage');
        }

        $credit = Portfolio_Credit::getInstance()->calculateCreditsFields($project_id);
        $stills = Portfolio_Asset::getInstance()->calculateAssetsFields($project_id, 'still');
        $process = Portfolio_Asset::getInstance()->calculateAssetsFields($project_id, 'process');

        $this->template->new_still_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($stills['new'])->getRender();
        $this->template->current_still_table_row = Template::getInstance()->render('current-asset-table-row')->iteratorParse($stills['current'])->getRender();


        $this->template->new_process_table_row = Template::getInstance()->render('new-asset-table-row')->iteratorParse($process['new'])->getRender();
        $this->template->current_process_table_row = Template::getInstance()->render('current-asset-table-row')->iteratorParse($process['current'])->getRender();

        $this->template->new_credit_table_row = Template::getInstance()->render('new-credit-table-row')->iteratorParse($credit['new'])->getRender();
        $this->template->current_credit_table_row = Template::getInstance()->render('current-credit-table-row')->iteratorParse($credit['current'])->getRender();

        $this->template->form_title = 'Edit Almost';
        $this->template->form_action = '/portfolio/edit-almost';
        $this->template->form_button = 'Save';
        $this->template->form = FormModel::getInstance()
            ->setResponse($response)
            ->setParams(Portfolio_Project::COLLECTION_NAME, $project_id, array('name', 'client_name', 'cover_fileid', 'hero_fileid', 'vimeo_url', 'video_embed1', 'video_embed2', 'video_embed3', 'video_embed4', 'video_embed5', 'video_embed6', 'video_embed7', 'video_embed8', 'video_embed9', 'video_embed10', 'description'))
            ->createForm();
        $this->template->center = array('portfolio-almost-form');
        $this->template->remove_hero = '<a href="/portfolio/remove-hero/'.$project_id.'">Remove Hero</a>';
        $this->setResponse($this->template);
    }

	protected function removeAction() {
		$this->requiresAuth();
		$this->path->setPath(array('project_id'))->getPathValues();
		$project_id = $this->memory->project_id;
		if($project_id > 0) {
			Portfolio_Project::getInstance()->remove($project_id);
		}
		Helper_Request::respond('/portfolio/manage');
	}

    protected function removeHeroAction() {
        $this->requiresAuth();
        $this->path->setPath(array('project_id'))->getPathValues();
        $project_id = $this->memory->project_id;
        $project = Portfolio_Project::getInstance();
        $project = $project->getProjectInfo($project_id);
        $category_id = $project['category_id'];
        $almost = $category_id == 2 ? '-almost' : '';
        if($project_id > 0) {
            Portfolio_Project::getInstance()->removeHero($project_id);
        }
        Helper_Request::respond('/portfolio/edit'.$almost.'/'.$project_id);
    }

	protected function removeAssetAction() {
		$this->requiresAuth();
		$this->path->setPath(array('asset_id', 'project_id'))->getPathValues();
		$asset_id = $this->memory->asset_id;
		$project_id = $this->memory->project_id;
        $project = Portfolio_Project::getInstance();
        $project = $project->getProjectInfo($project_id);
        $category_id = $project['category_id'];
        $almost = $category_id == 2 ? '-almost' : '';
		if($asset_id > 0) {
			Portfolio_Asset::getInstance()->remove($asset_id);
		}
		Helper_Request::respond('/portfolio/edit'.$almost.'/'.$project_id);
	}

    protected function removeCreditAction() {
        $this->requiresAuth();
        $this->path->setPath(array('credit_id', 'project_id'))->getPathValues();
        $credit_id = $this->memory->credit_id;
        $project_id = $this->memory->project_id;
        $project = Portfolio_Project::getInstance();
        $project = $project->getProjectInfo($project_id);
        $category_id = $project['category_id'];
        $almost = $category_id == 2 ? '-almost' : '';
        if($credit_id > 0) {
            Portfolio_Credit::getInstance()->remove($credit_id);
        }
        Helper_Request::respond('/portfolio/edit'.$almost.'/'.$project_id);
    }
}
