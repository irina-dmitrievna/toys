<?php
class ControllerExtensionModulemodulecreator extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/modulecreator');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		$this->load->model('localisation/language');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->createmodule($this->request->post['name']);
		}
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_select_all'] = $this->language->get('text_select_all');
		$data['text_unselect_all'] = $this->language->get('text_unselect_all');
		
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_files'] = $this->language->get('entry_files');
		$data['entry_setting_clone'] = $this->language->get('entry_setting_clone');
		$data['entry_module'] = $this->language->get('entry_module');
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		$data['name'] = '';
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/modulecreator', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/modulecreator', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['modulecreator_status'])) {
			$data['modulecreator_status'] = $this->request->post['modulecreator_status'];
		} else {
			$data['modulecreator_status'] = $this->config->get('modulecreator_status');
		}
		
		
		
		$data['categories'] = array();
		
		$files = glob(DIR_APPLICATION . 'controller/extension/extension/*.php', GLOB_BRACE);
		
		foreach ($files as $file) {
			$extension = basename($file, '.php');
			
			// Compatibility code for old extension folders
			$this->load->language('extension/extension/' . $extension, 'extension');
		
			if ($this->user->hasPermission('access', 'extension/extension/' . $extension)) {
				$files = glob(DIR_APPLICATION . 'controller/extension/' . $extension . '/*.php', GLOB_BRACE);
		
				$data['categories'][] = array(
					'code' => $extension,
					'text' => $this->language->get('extension')->get('heading_title'),
					'href' => $this->url->link('extension/extension/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
				);
				
				$module_list = glob(DIR_APPLICATION . 'controller/extension/' . $extension . '/*.php');
				foreach ($module_list as $module) {
					$data['module_list'][$this->language->get('extension')->get('heading_title')][] = $extension . '/' . basename($module, ".php");
				}
			}			
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/modulecreator', $data));
		
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/modulecreator')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	protected function rsearch($folder, $pattern) {
		$modulefiles = array();
		$iti = new RecursiveDirectoryIterator($folder);
		foreach(new RecursiveIteratorIterator($iti) as $file){
			 if(strpos($file , $pattern) !== false){
				$modulefiles[] = $file;
			 }
		}
		return $modulefiles;
	}
	protected function createmodule($modulename) {
		
		
		if ($this->request->post['module'] != '') {	
			
			$zipfile = DIR_CACHE . 'modulecreator/' . basename($this->request->post['module'], ".php") . '.ocmod.zip';
			$zip = new ZipArchive();
			$zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		
			$modulefiles = $this->rsearch(str_replace("/admin", "", DIR_APPLICATION), "/" . $this->request->post['module'] . ".");
		
			if (!file_exists(DIR_CACHE . 'modulecreator/')) {
				mkdir(DIR_CACHE . 'modulecreator/', 0777, true);
			}
			
			
			foreach ($modulefiles as $mfile) {
				$mfiledir = DIR_CACHE . 'modulecreator/upload/' . str_replace(str_replace("/admin", "", DIR_APPLICATION), "", $mfile->getPath());
				$zip->addFile($mfile->getRealPath(), 'upload/' . str_replace(str_replace("/admin", "", DIR_APPLICATION), "", $mfile->getRealPath()));
			}
			
		}
		else {
			$zipfile = DIR_CACHE . 'modulecreator/' . mb_strtolower(str_replace(' ', '', $modulename)) . '.ocmod.zip';
			$zip = new ZipArchive();
			$zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
			
			$crfile = $this->request->post['createfile'];
			
			if (!file_exists(DIR_CACHE . 'modulecreator/')) {
				mkdir(DIR_CACHE . 'modulecreator/', 0777, true);
			}
			
			if (in_array('admin/controller', $crfile)) {
				$filetext = "<?php
class ControllerExtension" . ucfirst($this->request->post['type']) . str_replace(' ', '', $modulename) . " extends Controller {
	private \$error = array();

	public function index() {
		
		";
		if (in_array('admin/model', $crfile)) {
			$filetext .= "\$this->load->model('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "');
			
		";
		}
		$filetext .= "\$this->load->language('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "');

		\$this->document->setTitle(\$this->language->get('heading_title'));
		
		";
		
		if ($this->request->post['setting_clone'] == 0) {
			$filetext .= "\$this->load->model('setting/setting');
		
		";
		}
		else {
			$filetext .= "\$this->load->model('setting/module');
		
		";
		}
		
		
		$filetext .= "if ((\$this->request->server['REQUEST_METHOD'] == 'POST') && \$this->validate()) {
			";
		if ($this->request->post['setting_clone'] == 0) {
			$filetext .= "\$this->model_setting_setting->editSetting('" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "', \$this->request->post);
			";
		}
		else {
			$filetext .= "if (!isset(\$this->request->get['module_id'])) {
				\$this->model_setting_module->addModule('" . mb_strtolower(str_replace(' ', '', $modulename)) . "', \$this->request->post);
			} else {
				\$this->model_setting_module->editModule(\$this->request->get['module_id'], \$this->request->post);
			}
			";
		}
			$filetext .= "

			\$this->session->data['success'] = \$this->language->get('text_success');

			\$this->response->redirect(\$this->url->link('marketplace/extension', 'user_token=' . \$this->session->data['user_token'] . '&type=" . $this->request->post['type'] . "', 'SSL'));
		}

		\$data['heading_title'] = \$this->language->get('heading_title');
		
		\$data['text_edit'] = \$this->language->get('text_edit');
		\$data['text_enabled'] = \$this->language->get('text_enabled');
		\$data['text_disabled'] = \$this->language->get('text_disabled');
		
		";
		if ($this->request->post['setting_clone'] != 0) {
			$filetext .= "\$data['entry_name'] = \$this->language->get('entry_name');
			";
		}
		$filetext .= "\$data['entry_status'] = \$this->language->get('entry_status');

		\$data['button_save'] = \$this->language->get('button_save');
		\$data['button_cancel'] = \$this->language->get('button_cancel');

		if (isset(\$this->error['warning'])) {
			\$data['error_warning'] = \$this->error['warning'];
		} else {
			\$data['error_warning'] = '';
		}
		
		";
		if ($this->request->post['setting_clone'] != 0) {
			$filetext .= "if (isset(\$this->error['name'])) {
			\$data['error_name'] = \$this->error['name'];
		} else {
			\$data['error_name'] = '';
		}
		
		";
		}

		$filetext .= "\$data['breadcrumbs'] = array();

		\$data['breadcrumbs'][] = array(
			'text' => \$this->language->get('text_home'),
			'href' => \$this->url->link('common/dashboard', 'user_token=' . \$this->session->data['user_token'], true)
		);

		\$data['breadcrumbs'][] = array(
			'text' => \$this->language->get('text_extension'),
			'href' => \$this->url->link('marketplace/extension', 'user_token=' . \$this->session->data['user_token'] . '&type=" . $this->request->post['type'] . "', true)
		);

		\$data['breadcrumbs'][] = array(
			'text' => \$this->language->get('heading_title'),
			'href' => \$this->url->link('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "', 'user_token=' . \$this->session->data['user_token'], true)
		);

		if (!isset(\$this->request->get['module_id'])) {
			\$data['action'] = \$this->url->link('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "', 'user_token=' . \$this->session->data['user_token'], true);
		} else {
			\$data['action'] = \$this->url->link('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "', 'user_token=' . \$this->session->data['user_token'] . '&module_id=' . \$this->request->get['module_id'], true);
		}
		
		\$data['cancel'] = \$this->url->link('marketplace/extension', 'user_token=' . \$this->session->data['user_token'] . '&type=module', true);
		
		";
		if ($this->request->post['setting_clone'] != 0) {
			$filetext .= "if (isset(\$this->request->get['module_id']) && (\$this->request->server['REQUEST_METHOD'] != 'POST')) {
			\$module_info = \$this->model_setting_module->getModule(\$this->request->get['module_id']);
		}
		
		if (isset(\$this->request->post['name'])) {
			\$data['name'] = \$this->request->post['name'];
		} elseif (!empty(\$module_info)) {
			\$data['name'] = \$module_info['name'];
		} else {
			\$data['name'] = '';
		}
		
		";
		}
		if ($this->request->post['setting_clone'] != 0) {
			
			$filetext .= "if (isset(\$this->request->post['status'])) {
			\$data['status'] = \$this->request->post['status'];
		} elseif (!empty(\$module_info)) {
			\$data['status'] = \$module_info['status'];
		} else {
			\$data['status'] = '';
		}
		
		";
		}
		else {
			$filetext .= "if (isset(\$this->request->post['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status'])) {
			\$data['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status'] = \$this->request->post['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status'];
		} else {
			\$data['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status'] = \$this->config->get('" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status');
		}

		";
			if ($this->request->post['type'] == 'payment') {
				
			$filetext .= "\$this->load->model('localisation/order_status');

			\$data['order_statuses'] = \$this->model_localisation_order_status->getOrderStatuses();
			
			";
		
			$filetext .= "if (isset(\$this->request->post['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_sort_order'])) {
			\$data['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_sort_order'] = \$this->request->post['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_sort_order'];
		} else {
			\$data['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_sort_order'] = \$this->config->get('" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_sort_order');
		}

		";
			$filetext .= "if (isset(\$this->request->post['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status_id'])) {
			\$data['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status_id'] = \$this->request->post['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status_id'];
		} else {
			\$data['" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status_id'] = \$this->config->get('" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_status_id');
		}

		";
			}
		}
		$filetext .= "\$data['header'] = \$this->load->controller('common/header');
		\$data['column_left'] = \$this->load->controller('common/column_left');
		\$data['footer'] = \$this->load->controller('common/footer');

		\$this->response->setOutput(\$this->load->view('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "', \$data));
	}
	
	protected function validate() {
		if (!\$this->user->hasPermission('modify', 'extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "')) {
			\$this->error['warning'] = \$this->language->get('error_permission');
		}
		
		";
		if ($this->request->post['setting_clone'] != 0) {
			$filetext .= "if ((utf8_strlen(\$this->request->post['name']) < 3) || (utf8_strlen(\$this->request->post['name']) > 64)) {
			\$this->error['name'] = \$this->language->get('error_name');
		}
		
		";
		}
		$filetext .= "return !\$this->error;
	}
}";
				$zip->addFromString("upload/admin/controller/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".php", $filetext);
			  }
			  if (in_array('admin/model', $crfile)) {
				$filetext = "<?php
class ModelExtension" . ucfirst($this->request->post['type']) . str_replace(' ', '', $modulename) . " extends Model {
}";
				$zip->addFromString("upload/admin/model/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".php", $filetext);
			  }
			  $languages = $this->model_localisation_language->getLanguages();
			  foreach ($languages as $language) {
				if (in_array('admin/language/' . $language['code'], $crfile)) {
				  if ($this->request->post['setting_clone'] == 0) {
					$filetext = file_get_contents(DIR_APPLICATION . 'language/' . $language['code'] .'/extension/module/category.php');
					$filetext = preg_replace('/.+heading_title.+/', "\$_['heading_title']    = '" . $this->request->post['title'][$language['language_id']] . "';", $filetext);
					
				  }
				  else {
					$filetext = file_get_contents(DIR_APPLICATION . 'language/' . $language['code'] .'/extension/module/html.php');
					$filetext = preg_replace('/.+heading_title.+/', "\$_['heading_title']    = '" . $this->request->post['title'][$language['language_id']] . "';", $filetext);
				  }
				  if ($this->request->post['type'] == 'payment') {
					$filetext .= "
\$_['entry_sort_order']   = 'Порядок сортировки';
\$_['entry_order_status'] = 'Статус заказа после оплаты';";
				  }
				  $zip->addFromString("upload/admin/language/" . $language['code'] ."/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".php", $filetext);
			    }
			  }
			  if (in_array('admin/view', $crfile)) {
				$filetext = '{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-' . $this->request->post['type'] . '" data-toggle="tooltip" title="{{ button_save }}" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="{{ cancel }}" data-toggle="tooltip" title="{{ button_cancel }}" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">
        {% for breadcrumb in breadcrumbs %}
        <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
        {% endfor %}
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    {% if error_warning %}
    <div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> {{ error_warning }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    {% endif %}
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> {{ text_edit }}</h3>
      </div>
      <div class="panel-body">
        <form action="{{ action }}" method="post" enctype="multipart/form-data" id="form-' . $this->request->post['type'] . '" class="form-horizontal">
		  ';
				$status_name = 'status';
				if ($this->request->post['setting_clone'] != 0) {
					$filetext .= '<div class="form-group">
            <label class="col-sm-2 control-label" for="input-name">{{ entry_name }}</label>
            <div class="col-sm-10">
              <input type="text" name="name" value="{{ name }}" placeholder="{{ entry_name }}" id="input-name" class="form-control" />
              {% if error_name %}
              <div class="text-danger">{{ error_name }}</div>
              {% endif %}
            </div>
          </div>  
		  ';
				}
				else {
					$status_name = '' . $this->request->post['type'] . '_' . mb_strtolower(str_replace(' ', '', $modulename)) . '_status';
				}
				$filetext .= '<div class="form-group">
            <label class="col-sm-2 control-label" for="input-status">{{ entry_status }}</label>
            <div class="col-sm-10">
              <select name="' . $status_name . '" id="input-status" class="form-control">
                {% if ' . $status_name . ' %}
                <option value="1" selected="selected">{{ text_enabled }}</option>
                <option value="0">{{ text_disabled }}</option>
                {% else %}
                <option value="1">{{ text_enabled }}</option>
                <option value="0" selected="selected">{{ text_disabled }}</option>
                {% endif %}
              </select>
            </div>
          </div>';
		  if ($this->request->post['type'] == 'payment') {
			  $filetext .= '
			  <div class="form-group">
				<label class="col-sm-2 control-label" for="input-order-status">{{ entry_order_status }}</label>
				<div class="col-sm-10">
				  <select name="' . $this->request->post['type'] . '_' . mb_strtolower(str_replace(' ', '', $modulename)) . '_order_status_id" id="input-order-status" class="form-control">
					{% for order_status in order_statuses %}
					{% if order_status.order_status_id == ' . $this->request->post['type'] . '_' . mb_strtolower(str_replace(' ', '', $modulename)) . '_order_status_id %}
					<option value="{{ order_status.order_status_id }}" selected="selected">{{ order_status.name }}</option>
					{% else %}
					<option value="{{ order_status.order_status_id }}">{{ order_status.name }}</option>
					{% endif %}
					{% endfor %}
				  </select>
				</div>
			  </div>';
			  $filetext .= '<div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order">{{ entry_sort_order }}</label>
            <div class="col-sm-10">
              <input type="text" name="' . $this->request->post['type'] . '_' . mb_strtolower(str_replace(' ', '', $modulename)) . '_sort_order" value="{{ ' . $this->request->post['type'] . '_' . mb_strtolower(str_replace(' ', '', $modulename)) . '_sort_order }}" placeholder="{{ entry_sort_order }}" id="input-sort-order" class="form-control" />
            </div>
          </div>';
		  }
        $filetext .= '
		</form>
      </div>
    </div>
  </div>
<?php echo $footer; ?>';
				$zip->addFromString("upload/admin/view/template/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".twig", $filetext);
			  }
			  if (in_array('catalog/controller', $crfile)) {
				$filetext = "<?php
class ControllerExtension" . ucfirst($this->request->post['type']) . str_replace(' ', '', $modulename) . " extends Controller {
	public function index(\$setting) {
		\$this->load->language('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "');
		
		";
				if (in_array('catalog/model', $crfile)) {
					$filetext .= "\$this->load->model('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "');
				
		";
				}
				$filetext .= "\$data['heading_title'] = \$this->language->get('heading_title');
		return \$this->load->view('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "', \$data);
	}";
	if ($this->request->post['type'] == 'payment') {
		$filetext .= "public function confirm() {
		\$json = array();
		
		if (\$this->session->data['payment_method']['code'] == '" . mb_strtolower(str_replace(' ', '', $modulename)) . "') {
			\$this->load->model('checkout/order');

			\$this->model_checkout_order->addOrderHistory(\$this->session->data['order_id'], \$this->config->get('" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_order_status_id'));
		
			\$json['redirect'] = \$this->url->link('checkout/success');
		}
		
		\$this->response->addHeader('Content-Type: application/json');
		\$this->response->setOutput(json_encode(\$json));		
	}";
	}
$filetext .= "}";
				$zip->addFromString("upload/catalog/controller/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".php", $filetext);
			  }
			  if (in_array('catalog/model', $crfile)) {
				$filetext = "<?php
class ModelExtension" . ucfirst($this->request->post['type']) . str_replace(' ', '', $modulename) . " extends Model {";
if ($this->request->post['type'] == 'payment') {
	$filetext .= "
	public function getMethod(\$address, \$total) {
		\$this->load->language('extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . "');

		\$method_data = array();

		\$method_data = array(
				'code'       => '" . mb_strtolower(str_replace(' ', '', $modulename)) . "',
				'title'      => \$this->language->get('heading_title'),
				'terms'      => '',
				'sort_order' => \$this->config->get('" . $this->request->post['type'] . "_" . mb_strtolower(str_replace(' ', '', $modulename)) . "_sort_order')
			);

		return \$method_data;
	}";
}
$filetext .= '
}';
				$zip->addFromString("upload/catalog/model/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".php", $filetext);
			  }
			  $languages = $this->model_localisation_language->getLanguages();
			  foreach ($languages as $language) {
				if (in_array('catalog/language/' . $language['code'], $crfile)) {
				  $filetext = "<?php
// Heading
\$_['heading_title'] = '" . $this->request->post['title'][$language['language_id']] . "';";
				  $zip->addFromString("upload/catalog/language/" . $language['code'] ."/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".php", $filetext);
			    }
			  }
			  if (in_array('catalog/view', $crfile)) {
				if ($this->request->post['type'] == 'payment') {
					$filetext = '<div class="buttons">
  <div class="pull-right">
    <input type="button" value="{{ button_confirm }}" id="button-confirm" data-loading-text="{{ text_loading }}" class="btn btn-primary" />
  </div>
</div>
<script type="text/javascript"><!--
\$(\'#button-confirm\').on(\'click\', function() {
	\$.ajax({
		url: \'index.php?route=extension/payment/" . mb_strtolower(str_replace(\' \', \'\', \$modulename)) . "/confirm\',
		dataType: \'json\',
		beforeSend: function() {
			$(\'#button-confirm\').button(\'loading\');
		},
		complete: function() {
			$(\'#button-confirm\').button(\'reset\');
		},
		success: function(json) {
			if (json[\'redirect\']) {
				location = json[\'redirect\'];	
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
//--></script>
';
				}
				else {
				$filetext = '<div>{% if heading_title %}
  <h2>{{ heading_title }}</h2>
  {% endif %}
</div>';
				}
				$zip->addFromString("upload/catalog/view/theme/default/template/extension/" . $this->request->post['type'] . "/" . mb_strtolower(str_replace(' ', '', $modulename)) . ".twig", $filetext);
			  }
			}

		$zip->close();
			
		header('Content-Type: application/zip');
		header('Content-Length: ' . filesize($zipfile));
		header('Content-Disposition: attachment; filename="' .  basename($zipfile) . '"');
		readfile($zipfile);
		
		unlink($zipfile);
    }
}