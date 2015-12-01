<?

	class MjmRestful_Module extends Core_ModuleBase {

        protected function createModuleInfo() {
			return new Core_ModuleInfo(
				"RESTful API Framework",
				"Enables RESTful API access points in your modules.",
				"Matt Manning (github:damanic)"
			);
		}

        public function listSettingsItems()
        {
            return array(
                array(
                    'icon'=>'/modules/mjmrestful/resources/images/restful_icon.png',
                    'title'=>'RESTful API Framework',
                    'url'=>'/mjmrestful/settings/config/',
                    'description'=>'Set restful service settings.',
                    'sort_id'=>80
                )
            );
        }

        /*
         * Default API Access point
         */
        public function register_access_points(){
            $api_settings = MjmRestful_SettingsManager::get();
            if($api_settings->disable_default_api){
                return array();
            }

            return array(
                'mjmapi'=>'api_v1',
            );
        }

        /*
         * Default REST API
         */
        public function api_v1($url_params){
            //load the MjmRestful router
            $router = new MjmRestful_Router();

            //add authentication layer to router
            $auth = new MjmRestful_Authenticate_Customer($router);

            //add locations to the router
            $locations = new MjmRestful_Location_API($router);

            //we runnin json
            $run_options = array('format'=>'json');

            //RUN API APP
            $router->run($url_params,$run_options);
        }

    }
