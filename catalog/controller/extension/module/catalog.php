<?php
class ControllerExtensionModulecatalog extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/catalog');
		
		$data['heading_title'] = $this->language->get('heading_title');
		return $this->load->view('extension/module/catalog', $data);
	}}