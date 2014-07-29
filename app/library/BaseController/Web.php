<?php
class BaseController_Web extends ControllerSite {
	protected $css_modules = array(
		'base.css',
		'markitup.css'
	);
	protected $auth;
	public function __construct() {
		parent::__construct();
		$this->auth = Authentication::create();
	}

	protected function getPage($template, $active = 'work') {
		$this->setGlobalProperties($template);
        $this->setActive($template, $active);
	}

	protected function setGlobalProperties($template) {
		$template->url = Config::$url;
		$template->uri = Config::$uri;
		$template->static_host = Config::$static_host;
		$template->google_tracking_key = Config::$tracking_code['google'];
		$template->favicon_url = Config::$favicon;
		$template->web_name = Config::WEB_NAME;
		$template->developer = Config::DEVELOPER;
		$template->control_panel = $this->getUser() ? Template::getInstance()->render('control-panel')->getRender() : '';
	    $template->submenu = '';

        if ($_COOKIE['ashy']) {
            $this->template->footer_info = '';
        } else {
            $this->template->footer_info = '<ul class="social-networks">
            <li><a href="#" target="_blank">vimeo</a></li>
            <li class="facebook"><a href="https://www.facebook.com/sharer/sharer.php?u=http://www.scoutstudios.tv" target="_blank">facebook</a></li>
            <li class="twitter"><a href="https://twitter.com/intent/tweet?text=Scout Studios&url=http://www.scoutstudios.tv" target="_blank">twitter</a></li>
            <li class="email"><a href="mailto:?subject=Scout Studios&body=%0D%0A%0D%0Ahttp://www.scoutstudios.tv">email</a></li>
        </ul>
        <address><span>532 Broadway <span class="spare-spaces">&nbsp;&nbsp;</span>5th floor<br/><span class="spare-spaces">&nbsp;&nbsp;</span> New York<span class="spare-spaces">&nbsp;</span>&nbsp;&nbsp;NY <span class="spare-spaces">&nbsp;&nbsp;</span>10012</span> <span class="spare-spaces">&nbsp;&nbsp;</span><a class="tel" href="tel:6465568118"> T: 646.556.8118</a></address>';
        }
    }

    protected function setActive($template, $active) {
        $template->work_active = ($active == 'work' || $active == 'almost' || $active == 'work-project') ? 'active' : '';
        $template->montage_active = ($active == 'montage') ? 'active' : '';
        $template->about_active = ($active == 'about') ? 'active' : '';
        $template->blog_active = ($active == 'blog') ? 'active' : '';
        $template->contact_active = ($active == 'contact') ? 'active' : '';

        switch($active) {
            case 'work-project':
                $template->body_class = 'work inner';
                break;
            case 'work':
                $template->body_class = 'work';
                break;
            case 'almost':
                $template->body_class = 'work inner4';
                break;
            case 'montage':
                $template->body_class = 'montage';
                break;
            case 'about':
                $template->body_class = 'about';
                break;
            case 'blog':
                $template->body_class = 'blog';
                break;
            case 'contact':
                $template->body_class = 'contact';
                break;
            default:
                $template->body_class = 'home';
                break;
        }
    }

	protected function requiresAuth() {
		if(!$this->getUser()) {
			$this->auth->deauthorize();
			Helper_Request::respond(FacebookWrapper::create()->getLogoutURL());
		}
	}

}