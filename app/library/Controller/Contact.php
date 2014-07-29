<?php
class Controller_Contact extends BaseController_Web {

	protected function defaultAction() {
		parent::getPage($this->template, 'contact');
		$this->template->title = 'Contact';
		$this->template->center = array('contact');

        if ($_COOKIE['ashy']) {
            $this->template->rep_type = 'Exclusive Broadcast Representation by:';
            $this->template->contact = '
                    <div class="holder">
                        <span class="name">The Ashy Agency<br/>
Brett Ashy<br/>
323-653-2749</span>
                        <a href="mailto:Brett@ashyagency.com">Brett@ashyagency.com</a>
                    </div>';
            $this->template->people_info = '';
            $this->template->tel_one = '';
            $this->template->tel_two = '';
        } else {
            $this->template->rep_type = 'Representation';
            $this->template->contact = '<div class="holder">
                        <span class="name">Katie Northy</span>
                        <a href="mailto:&#107;&#097;&#116;&#105;&#101;&#064;&#115;&#099;&#111;&#117;&#116;&#115;&#116;&#117;&#100;&#105;&#111;&#115;&#046;&#116;&#118;">&#107;&#097;&#116;&#105;&#101;&#064;&#115;&#099;&#111;&#117;&#116;&#115;&#116;&#117;&#100;&#105;&#111;&#115;&#046;&#116;&#118;</a>
                    </div>
                    <div class="holder">
                        <span class="name">Jessica Millington</span>
                        <a href="mailto:&#106;&#101;&#115;&#115;&#105;&#099;&#097;&#064;&#115;&#099;&#111;&#117;&#116;&#115;&#116;&#117;&#100;&#105;&#111;&#115;&#046;&#116;&#118;">&#106;&#101;&#115;&#115;&#105;&#099;&#097;&#064;&#115;&#099;&#111;&#117;&#116;&#115;&#116;&#117;&#100;&#105;&#111;&#115;&#046;&#116;&#118;</a>
                    </div>
                    ';
            $this->template->people_info = ' <div class="box">
                <strong class="title">Executive Producer</strong>
                <div class="holder">
                <span class="name">Stephanie Balint</span>
                <a href="mailto:stephanie@scoutstudios.tv">stephanie@scoutstudios.tv</a>
                </div>
                </div>
                <div class="box">
                <strong class="title">Head of Production</strong>
                <div class="holder">
                <span class="name">Whitney Green</span>
                <a href="mailto:&#119;&#104;&#105;&#116;&#110;&#101;&#121;&#064;&#115;&#099;&#111;&#117;&#116;&#115;&#116;&#117;&#100;&#105;&#111;&#115;&#046;&#116;&#118;">&#119;&#104;&#105;&#116;&#110;&#101;&#121;&#064;&#115;&#099;&#111;&#117;&#116;&#115;&#116;&#117;&#100;&#105;&#111;&#115;&#046;&#116;&#118;</a>
                </div>
                </div>';
            $this->template->tel_one = '<a class="tel" href="tel:6465568118">T: 646.556.8118</a>';
            $this->template->tel_two = '<a class="tel" href="tel:3105819900">T: 310.581.9900</a>';

        }

		$this->setResponse($this->template);
	}
}
