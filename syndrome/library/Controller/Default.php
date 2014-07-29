<?php
/**
 * Main controller for handling the base path
 *
 * @author Man Hoang
 * @name Controller_Default
 */
class Controller_Default extends ControllerSite {

	/**
	 * The default action to be taken if the request action doesn't exist
	 *
	 * @return ControllerSite
	 */
	protected function defaultAction() {
		return $this->forward(Config::DEFAULT_CONTROLLER);
	}
}