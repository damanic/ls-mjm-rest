<?

/* @TODO
 * Options are descriptions in the response header, that provide information about the API.
 * Use it as such.
 *
 * Do not allow a route to override the RUN format option.
 *
 *
 *
 * */

	class MjmRestful_Router {

        protected $options;
        protected $routes;
        protected $reserved_routes;
        protected $auth;
        public $get_cache_handler;
        public $set_cache_handler;

		public function __construct($options = array()) {
			$this->options = Core_Array::merge_recursive_distinct(array(
				'caching' => false,
				'cache_time' => 0,
				'routes' => array('GET' => array(), 'POST' => array(), 'PUT' => array(), 'PATCH' => array(), 'DELETE' => array())
			), $options);
		}

        public function enable_authentication(MjmRestful_Authenticate $auth){
        $this->auth = $auth;
        }


		public function set_options($options) {
			foreach($options as $name => $value)
				$this->options[$name] = $value;
		}
	
		public function set_option($name, $value) {
			$this->options[$name] = $value;
		}
	
		public function run($request_url_params, $options = array()) {
			$options = Core_Array::merge_recursive_distinct(array(
				'format' => 'object',
				'types' => array('GET','POST','PUT','PATCH','DELETE'),

			), $options);
			
			extract($options);

            //@TODO CACHING


				try{

						foreach($this->routes as $route) {

                            //check run allows route type
                            if(!in_array($route->get_type(), $types)){
                            continue;
                            }

                            //check correct request type. Options available for all routes
                            if(!in_array(Phpr::$request->getRequestMethod(),  array($route->get_type(), 'OPTIONS'))){
                            continue;
                            }

                            //run route
                            $response = $route->run_route($request_url_params);

							if($response)
								break;
						}
				}
				catch(Exception $e1) {
					Phpr::$errorLog->logException($e1);
                    $response = MjmRestful_Response::create('internal_error', null, 'An error was encountered during routing. Please have the administrator check the error log.');
                }



            if(!is_a($response, 'MjmRestful_Response')) {
            $response =  MjmRestful_Response::create('not_implemented', null, 'The requested route is not implemented correctly.');
            }

            if(empty($response->format)){
            $response->format = $format; //@TODO clarify where and how format should be set
            }
        $this->respond($response);
		}

        protected function respond(MjmRestful_Response $response){
            //deliver headers
            $response->deliver_headers();
            switch($response->format) {
                case 'object': $response->content; break;
                case 'xml': $this->respond_xml($response->content); break;
                case 'json': $this->respond_json($response->content); break;
                case 'options': break; //No content on an option response.
                //case 'rss': break; //@TODO
            }
        }


        protected function respond_xml($response){
           header("Content-Type: application/xml");
           echo MjmRestful_Helper::xml_encode($response, 'result');
        }

        protected function respond_json($response){
            header("Content-Type: application/javascript");

            if($method = Phpr::$request->getField('callback'))
                echo $method . '(' . MjmRestful_Helper::json_encode($response) . ');';
            else
                echo MjmRestful_Helper::json_encode($response);
        }


        public function reserve_route($route){
            //do not allow override of these routes once set.
            $this->reserved_routes[$route->get_type()][] = $route->get_route_path();
        }

        public function is_reserved($path, $type){
            //get route path without parameters
            $path = MjmRestful_Route::tidy_pattern($path);
            $path = MjmRestful_Route::extract_route_path($path);
            if(in_array($path,$this->reserved_routes[$type])){
            return true;
            }
        return false;
        }

		public function add_route($type, $path, $callback, $options=NULL) {
           // if(!$this->is_reserved($path, $type)){
            $route = new MjmRestful_Route($type, $path, $callback, $options, $this->auth);
            $this->routes[] = $route;
            $this->options['routes'][$type][$path] = $options;
           // } else {
            //throw new Phpr_ApplicationException('Cannot add route. Path '.$path.' is reserved');
           // }
		//return $route;
        }
		
		public function get($path, $callback, $options=NULL) {
			return $this->add_route('GET', $path, $callback, $options);

		}
		
		public function post($path, $callback, $options=NULL) {
			return $this->add_route('POST', $path, $callback, $options);
		}
			
		public function put($path, $callback, $options=NULL) {
			return $this->add_route('PUT', $path, $callback, $options);
		}


        public function patch($path, $callback, $options=NULL) {
            return $this->add_route('PATCH', $path, $callback, $options);
        }
		
		public function delete($path, $callback, $options=NULL) {
			return $this->add_route('DELETE', $path, $callback, $options);
		}



	}





