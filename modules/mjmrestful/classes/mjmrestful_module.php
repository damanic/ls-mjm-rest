<?
/* @TODO
 *  - Allow to set header OPTIONS on router __construct
 *  - Auto add routes to header OPTIONS
 *  - Allow set config on router run()
 *  - Allow set config on route add()
 *  - Force HTTPS on authentication routes by default
 *  - Allow caching
 *  - Clean up trigger to delete old keys
 *  - Set up a Config page to set:
 *      - API TOKEN NAME
 *      - API TOKEN EXPIRY IN DAYS
 *      - LOCK TOKEN TO DEVICE+IP  ON/OFF
 *      - force_correct_request_type  !!!! REDUNDANT SEE  = !*!&!
 *      - force HTTPS for authentication ON/OFF
 *  = !*!&! A path must be able to take combination of PUT/GET/POST/DELETE
 *          and route correctly based on detected request type.
 *  - GET parameters Could be supported eg.  /products/?q=search_query
 *  - Logout / Delete Token Route. Reserved
 *  - MjmRestful_Authenticate is extended by MjmRestful_AuthCustomer and MjmRestful_AuthAdmin
 *  - MjmRestful_Router takes and extension of MjmRestful_Authenticate, defaults to MjmRestful_AuthCustomer
 *  -
 *
 *
 */

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
 * Access points
 */
        public function register_access_points(){
            return array(
                'restful_api'=>'api'
            );
        }

        /**
         * REST API
         */
        public function api($url_params){

            //load the restful router
            $router = new MjmRestful_Router();


            //add Location Resource API to Router
            $api = new MjmRestful_Location_API($router);

            //we runnin json
            $run_options = array('format'=>'json');

            //RUN API APP
            $router->run($url_params,$run_options);
        }


    }
