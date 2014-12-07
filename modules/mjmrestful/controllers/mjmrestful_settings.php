<?

	class MjmRestful_Settings extends Backend_SettingsController
	{
		public $implement = 'Db_ListBehavior, Db_FormBehavior';
		public $form_model_class = '';
		protected $access_for_groups = array(Users_Groups::admin);


		public function config()
		{
			$this->app_page_title = 'RESTful Settings';
			$this->form_model_class = 'MjmRestful_SettingsManager';
			
			$settings = MjmRestful_SettingsManager::get();
			$settings->init_columns_info();
			$settings->define_form_fields();
			$this->viewData['settings'] = $settings;
		}
		
		protected function config_onSave()
		{
			try
			{
				$settings = MjmRestful_SettingsManager::get();
				$settings->init_columns_info();
				$settings->define_form_fields();

				$settings->save(post('MjmRestful_SettingsManager'));

				Phpr::$session->flash['success'] = 'Restful settings have been successfully saved.';
				Phpr::$response->redirect(url('mjmrestful/settings/config/'));
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
	}
	
?>