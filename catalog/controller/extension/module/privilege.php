<?php
class ControllerExtensionModuleprivilege extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/privilege');
		
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/privilege', $data);
	}}