<?php
class Controller_Ashy extends BaseController_Web {

    protected function defaultAction() {
        setcookie('ashy', 'yes', time() + 86400 * 14, '/');
        Helper_Request::respond(Config::$url, Helper_Request::RESPONSE_REDIR, Config::$platform);
    }
}