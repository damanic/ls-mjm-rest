<?php

	class MjmRestful_SettingsManager extends Backend_SettingsRecord
	{
		public $table_name = 'mjmrestful_settings';
		public static $obj = null;
		
		public $enable_filebased_templates = false;

		public static function get($className = null, $init_columns = true)
		{
			if (self::$obj !== null)
				return self::$obj;
			
			return self::$obj = parent::get('MjmRestful_SettingsManager');
		}

		public function define_columns($context = null)
		{
			$this->validation->setFormId('settings_form');
            $this->define_column('token_expire', 'Expire Time for Auth Tokens')->validation()->required('Please specify token expiry'); //
            $this->define_column('token_header_name', 'Token Header Name')->validation()->required('Please specify token header');
            $this->define_column('token_device_lock', 'Lock Token to Device/IP');
            $this->define_column('force_https', 'Require HTTPS');
            $this->define_column('force_correct_request', 'Enforce Correct Request Type');

           }
		
		public function define_form_fields($context = null)
		{
            $this->add_form_field('token_header_name')->comment('Set the header name to be used for AUTH token checks', 'above');
			$this->add_form_field('token_expire')->comment('Set how many days an AUTH token should last', 'above');
			$this->add_form_field('token_device_lock')->renderAs(frm_onoffswitcher)->comment('If lock is set to on, tokens will only be valid for the IP and Device it was issued to', 'above');
            $this->add_form_field('force_https')->renderAs(frm_onoffswitcher)->comment('Set if secure HTTPS connections required - highly recommended if routes require authorisation', 'above');
            $this->add_form_field('force_correct_request')->renderAs(frm_onoffswitcher)->comment('If set to on, all requests must match the route type. Eg. A DELETE route will not respond to a GET request', 'above');

        }

        /*
		public function get_force_https_options($key_value = -1){
			$options = array(
				'all'=>'Required',
				'auth'=>'Required for Auth Only',
                'none'=>'Not Required'
			);
				
			return $options;
		}
        */
		

		public function before_save($deferred_session_key = null) 
		{

		}
		

	}

?>