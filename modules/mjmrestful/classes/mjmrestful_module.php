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

    }
