<?php
class Controller_ErrorPage extends BaseController_Web {

	/**
	 * The default action to be taken if the request action doesn't exist
	 *
	 * @return ControllerSite
	 */
	protected function defaultAction() {
		return $this->forward(Config::ERROR_PAGE_CONTROLLER);
	}
}