<?php

class MjmRestful_Response {

    public $content;
    public $status_code;
    public $message;
    public $format;
    public $headers;



    public static function create($type, $data, $message=NULL, $format=NULL){
        $obj = new self;
        $obj->$type($data, $message, $format);

        if($format){
        $obj->format = $format;
        }

        return $obj;
    }


    public function ok($response, $message=NULL){
    $this->message = $message ? $message : 'The request was successful';
    $this->status_code = 200;
    $this->content = $response;
    return $this;
    }

    public function bad_request($response, $message=NULL){
    $this->message = $message ? $message : 'There was a problem with the request';
    $this->status_code  = 400;
    $this->content = $response;
    return $this;
    }

    public function unauthorised($response, $message=NULL){
        $this->message = $message ? $message : 'Authenticated users only';
        $this->status_code  = 401;
        $this->content = $response;
        return $this;
    }

    public function internal_error($response, $message=NULL){
    $this->message = $message ? $message : 'Internal Server Error';
    $this->status_code  = 500;
    $this->content = $response;
    return $this;
    }

    public function not_implemented($response, $message=NULL){
    $this->message = $message ? $message : 'Not Implemented';
    $this->status_code  = 501;
    $this->content = $response;
    return $this;
    }

    public function add_headers($header, $value){
        $this->headers[$header] = $value;
    }

    public function add_access_allow_methods($methods){
        $this->add_headers('Access-Control-Allow-Methods', implode(', ',$methods));
    }

    public function add_access_allow_origin($string){
        $this->add_headers('Access-Control-Allow-Origin', $string);
    }

    public function deliver_headers(){
        $api_settings = MjmRestful_SettingsManager::get();
        header("HTTP/1.0 {$this->status_code} {$this->message}");

        if(!isset($this->headers['Access-Control-Allow-Headers'])){
            $this->add_headers('Access-Control-Allow-Headers', 'Content-Type, '.$api_settings->token_header_name.', '.MjmRestful_Helper::get_header('Access-Control-Request-Headers'));
        }
        if(!isset($this->headers['Access-Control-Max-Age'])) {
            $this->add_headers('Access-Control-Max-Age', '86400');
        }
        if(!isset($this->headers['Access-Control-Allow-Origin'])) {
            $this->add_access_allow_origin('*');
        }

        foreach($this->headers as $header =>  $value){
            if(!empty($value)){
                header("{$header}: {$value}");
            }
        }
    }

    //Close user connection with OK response and allow scripts to continue
	//WARNING: Has potential to terminate script execution on some stacks. TEST!
    public static function close_continue(){
		ignore_user_abort(true); //continue script after connection is closed
		ob_end_clean(); //clear buffering
		header("Connection: close\r\n");
		header("Content-Encoding: none\r\n");
		ob_start();
		echo 0;
		$size = ob_get_length();
		header("Content-Length: $size");
		if(version_compare(PHP_VERSION, '5.4.0') >= 0) {
			http_response_code(200);
		}
		ob_end_flush();
		@ob_flush();
		flush();
		ob_end_clean();
		//@fastcgi_finish_request();
	}




}