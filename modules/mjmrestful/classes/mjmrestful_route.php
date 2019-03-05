<?php
class MjmRestful_Route {
    protected $type;
    protected $pattern;
    protected $route_path;
    protected $callback;
    protected $auth;
    public $options;

    /* @TODO
     * A more flexible way to identify the route path and variable parameters.
     * Currently:
     * must be /route/path/:variable1/:variable2
     * cannot be /route/:variable1/path/:variable2
     *
     * Also does not consider named parameters in the url string
     * currently cannot do /route/path/?named_var=value
     *
     */
    public function __construct($type, $pattern, $callback, $options, MjmRestful_Authenticate $auth = null) {
        $this->set_type($type);
        $this->set_pattern($pattern);
        $this->set_callback($callback);

        if($auth){
			$this->set_auth($auth);
        }

        $this->options = Core_Array::merge_recursive_distinct(array(
            'requires_authentication' => false,
            'allow_methods' => array('GET','POST','PUT','DELETE','OPTIONS','PATCH')
        ), $options);

    }

	public function set_requires_authentication($val=true){
		$this->options['requires_authentication'] = $val;
	}

    public function requires_authentication(){
        if($this->options['requires_authentication']){
            return true;
        }
    return false;
    }

	public function set_auth(MjmRestful_Authenticate $auth){
		$this->auth = $auth;
	}
    protected function set_type($type){
        $allowed_types = array('GET','POST','PUT','DELETE','PATCH');
        if(in_array($type,$allowed_types)){
            $this->type = $type;
            return;
        }
        throw new Phpr_ApplicationException('Cannot set route type as '.$type);
    }

    public function get_type(){
        return $this->type;
    }

    public function set_pattern($pattern){
        $this->pattern = self::tidy_pattern($pattern);
        $this->set_route_path();
    }

    public static function tidy_pattern($pattern){
        //remove trailing slashes.
        $pattern= rtrim($pattern, '/');
        //force starting slash
        $pattern= ltrim($pattern, '/');
        $pattern = '/'.$pattern;
        return $pattern;
    }

    public function get_pattern(){
        return $this->pattern;
    }


    protected function set_route_path(){
        $this->route_path = $this->extract_route_path($this->pattern).'/';
    }

    public function get_route_path(){
        return $this->route_path;
    }

    public static function extract_route_path($pattern){
        if(strstr($pattern, '/:')){
            $route_path = explode('/:',$pattern);
            $route_path = $route_path[0];
        } else {
            $route_path = $pattern;
        }
        return $route_path;
    }

    protected function set_callback($callback){

        if(!is_array($callback) && !is_object($callback[0]) && !is_string($callback[1]))
        throw new Phpr_Exception('Callback must be an array of object,method');


        if(!is_callable($callback))
            throw new Phpr_Exception('Callback could not be called');

        $this->callback = $callback;
    }

    public function run_route($request_url_parameters){

        //check correct route path for run
        if($this->is_accepted_route_path($request_url_parameters)){

            //serve options if requested
            if(Phpr::$request->getRequestMethod() == 'OPTIONS'){
            return $this->return_option_response();
            }

            //check authentication
            if($this->requires_authentication()){

                if(!$this->auth || !$this->auth->is_user_authenticated()){
                return MjmRestful_Response::create('unauthorised', null, 'You are not authorised to use this resource');
                }
            }

            $response = call_user_func_array($this->callback, array($this));
            if($response) {
				$response->add_access_allow_methods( $this->options['allow_methods'] );
				return $response;
			} else {
            	traceLog('API ROUTE ERROR: ');
            	traceLog($this->callback);
			}
        }
    return false;
    }

    public function return_option_response(){
        $response = MjmRestful_Response::create('ok', null, 'route options');
        $response->add_access_allow_methods($this->options['allow_methods']);
        $response->add_headers('Content-Length', 0);
        $response->add_headers('Content-Type','text/plain');
        $response->format = 'options';
        return $response;
    }

    public function populate_url_parameters($request_url_parameters){
        $split_pattern = explode('/:',$this->pattern);
        array_shift($split_pattern);
        $param_position = $this->count_expected_route_params();

        foreach($split_pattern  as $param_id){
            $this->get_parameters[$param_id] = $request_url_parameters[$param_position];
            $param_position++;
        }
    }

    public function get_url_param($key){
        return  $this->get_parameters[$key];
    }


    //check array of request parameters to see if it is valid for this route.
    public function is_accepted_route_path(Array $request_url_parameters){

        if(count($request_url_parameters) > $this->count_expected_pattern_params()){
           return false; // strict, we will not accept extra parameters in the url that are not defined in the route pattern.
        }

        $request_path = $this->return_request_url_array_as_string($request_url_parameters);
       // $this->echo_log('route path '.$this->route_path.' in request path '.$request_path.' = '.strpos($request_path , $this->route_path));

        if(strpos($request_path, $this->route_path) === 0){

           //attach any valid get parameters in the request URL to this route.
           $this->populate_url_parameters($request_url_parameters);


            return true; // route matched, you can serve.
        }

        return false;
    }

    protected function return_request_url_array_as_string($request_url_parameters){
        return '/'.implode('/',$request_url_parameters).'/';
    }

    protected function count_expected_get_params(){
        $this->echo_log(substr_count($this->pattern, '/:'));
        return substr_count($this->pattern, '/:');
    }

    protected function count_expected_route_params(){
        $result = substr_count($this->pattern, '/') - $this->count_expected_get_params();
        $this->echo_log($result );
        return $result;
    }

    protected function count_expected_pattern_params(){
        $expected = $this->count_expected_get_params() +  $this->count_expected_route_params();
        $this->echo_log($expected);
        return $expected;

    }

    public function echo_log($details){
        return;
        $callers=debug_backtrace();
        echo $this->pattern.' '.$callers[1]['function'].' | '.$details.'<br/>';
    }

}