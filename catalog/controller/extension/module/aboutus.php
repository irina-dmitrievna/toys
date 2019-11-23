<?php
class ControllerExtensionModuleaboutUs extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/aboutus');
		
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/aboutus', $data);
	}}