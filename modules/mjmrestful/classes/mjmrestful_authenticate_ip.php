<?php

class MjmRestful_Authenticate_Ip extends MjmRestful_Authenticate {
    protected $api_settings;
	protected $allowed_ips = array();
	protected $access_pw;

    public function __construct(MjmRestful_Router $router){

        //PASS authentication to the router.
        $router->enable_authentication($this);

        //inherit routers api settings
        $this->api_settings = $router->get_api_settings();

    }

    public function get_api_settings(){
        if(empty($this->api_settings)){
            $this->api_settings = MjmRestful_SettingsManager::get();
        }
        return $this->api_settings;
    }

	public function add_ip($ip){
		$this->allowed_ips[] = $ip;
	}

	public function remove_ip($ip){
		if(($key = array_search($ip, $this->allowed_ips)) !== false) {
			unset($this->allowed_ips[$key]);
		}
	}

	public function has_ip_restrictions(){
    	if(count($this->allowed_ips)){
    		return true;
		}
    	return false;
	}

	public function set_ips($ips=array()){
		$this->allowed_ips = $ips;
	}

	public function set_access_pw($pw){
		$this->access_pw = $pw;
	}

	public function requires_access_pw(){
		if(!empty($this->access_pw)){
			return true;
		}
		return false;
	}


    public function  is_user_authenticated(){

    	if($this->has_ip_restrictions()) {
			if ( !in_array( Phpr::$request->getUserIp(), $this->allowed_ips ) ) {
				return false;
			}
		}

		if($this->requires_access_pw()){
			$input_stream = MjmRestful_Helper::get_input_stream();
			$submitted_pw = MjmRestful_Helper::get_post_json('access_pw',$input_stream);
				if(!$submitted_pw || $submitted_pw != $this->access_pw){
					return false;
				}
		}
    return true;
    }



}