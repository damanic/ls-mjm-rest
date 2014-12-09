<?php

class MjmRestful_Authenticate_Customer extends MjmRestful_Authenticate {
protected $fields_allow_read;
protected $fields_allow_write;

    public function __construct(MjmRestful_Router $router){

        //PASS authentication to the router.
        $router->enable_authentication($this);

        //Default Read Fields
        $this->allow_customer_fields_read(array('id', 'first_name','last_name','email','company','phone'));


        //Set Reserved Authentication Routes.
        //GET
        //Fetch Authenticated Customer Account (can provide LOGIN credentials)
        $callback = array(&$this, 'get_customer');
        $route = $router->get('/session/customer/:id', $callback);
        //$this->reserve_route($route);

        //GET POST PUT
        //Resets Customer Password, sends new one via email
        $callback = array(&$this, 'reset_customer_pw');
        $route = $router->get('/session/customer_reset_pw/', $callback);
        $route = $router->post('/session/customer_reset_pw/', $callback);
        $route = $router->put('/session/customer_reset_pw/', $callback);
        //$this->reserve_route($route);

        //POST
        //Register Customer Account
        $path = '/session/customer/';
        $callback = array(&$this, 'register_customer');
        $route = $router->post($path,$callback);
        // $this->reserve_route($route);

        //PUT
        //Update Customer Account
        $path = '/session/customer/:id';
        $callback = array(&$this, 'update_customer');
        $options = array('requires_authentication'=>true);
        $route = $router->put($path,$callback, $options);
        //$this->reserve_route($route);

        //DELETE
        //Logout Customer
        $path = '/session/customer/:id';
        $callback = array(&$this, 'logout_customer');
        $options = array('requires_authentication'=>true);
        $route = $router->delete($path,$callback, $options);
        //$this->reserve_route($route);
    }

    public function allow_customer_fields_write(Array $fields){
    $this->fields_allow_write = $fields;
    }

    public function allow_customer_fields_read(Array $fields){
    $this->fields_allow_read = $fields;
    }

    public function get_customer_fields_read(){
    return $this->fields_allow_read;
    }

    public function get_customer_fields_write(){
    return $this->fields_allow_write;
    }

    public function  is_user_authenticated(){
        $customer = Phpr::$frontend_security->authorize_user();
        if ($customer){
            return true;
        }

        if($this->login_on_key()){
            return true;
        }
    return false;
    }

    public function login_on_key(){
        if(strlen(self::get_users_api_token()) > 32){

            //check for valid key
            $customer = MjmRestful_ApiAccess::get_user_by_token(self::get_users_api_token(),
                self::get_users_device_id());

            if($customer){
                //login customer
                Phpr::$frontend_security->customerLogin($customer->id);
                return true;
            }
        }
    return false;
    }

    public function register_customer(){
        try{
        $customer = new Shop_Customer();
        $customer->disable_column_cache('front_end', false);
        $customer->init_columns_info('front_end');
        $customer->validation->focusPrefix = null;
        $customer->validation->getRule('email')->focusId('signup_email');

        $input_stream = MjmRestful_Helper::get_input_stream();

         foreach($this->get_customer_fields_write() as $field){
             $value = MjmRestful_Helper::get_post_json($field,$input_stream);
             if($value){
             $lm_data[$field] = $value;
             }
         }

        //required fields
        $lm_data['email'] = MjmRestful_Helper::get_post_json('email',$input_stream);
        //require password
        $lm_data['password'] = MjmRestful_Helper::get_post_json('password',$input_stream);
        $lm_data['password_confirm'] = MjmRestful_Helper::get_post_json('password_confirm',$input_stream);
            if(empty($lm_data['password_confirm'])){
                $lm_data['password_confirm'] = $lm_data['password'];
            }

        //save data, send confirmation and log in
        $customer->save($lm_data);
        $customer->send_registration_confirmation();
        Phpr::$frontend_security->customerLogin($customer->id);

        //create a token for future access
        $token = $this->renew_token($customer);
        $data = new stdClass();
        $data->access_token = $token;
        $data = $this->get_customer_data($customer, $data);

            return MjmRestful_Response::create('ok', $data, 'Thank you for Registering');
        } catch (Exception $e){
            return MjmRestful_Response::create('bad_request', null, $e->getMessage());
        }
    }

    public function update_customer(){
        //GET CUSTOMER OBJ
        if($this->is_user_authenticated()){
            $customer = Phpr::$frontend_security->authorize_user();
        } else {
            return MjmRestful_Response::create('unauthorised', null, 'Not logged in');
        }

        try{
        $allowed_fields = $this->get_customer_fields_write();
        $input_stream = MjmRestful_Helper::get_input_stream();
        $data = new stdClass();
            foreach($allowed_fields as $field){
                $value = MjmRestful_Helper::get_post_json($field,$input_stream);
                $data->$field = $value;
            }

            //passwords must be set to null otherwise password reset
            if(!isset($data->password) || empty($data->password)){
                $data->password = NULL;
            }

        $customer->save($data);
        return MjmRestful_Response::create('ok', $this->get_customer_data($customer), 'Updated Customer');
        }
        catch (Exception $ex) {
        return MjmRestful_Response::create('bad_request',$data, $ex->getMessage());
        }
    }

    public function reset_customer_pw(){
        try{
            $validation = new Phpr_Validation();
            $validation->add('email', 'Email')->fn('trim')->required('Please specify your email address')->email()->fn('mb_strtolower');
            $data['email'] = MjmRestful_Helper::get_post_json('email');

            if (!$validation->validate($data))
                 $validation->throwException();

            Shop_Customer::reset_password($validation->fieldValues['email']);
            return MjmRestful_Response::create('ok', $data, 'Reset Customer Password');
        }
        catch (Exception $ex) {
           return MjmRestful_Response::create('bad_request',$data, $ex->getMessage());
        }

    }


    public function get_customer(){

        //allow key access
        if($this->is_user_authenticated()){
        $customer = Phpr::$frontend_security->authorize_user();
        } else {
        return $this->try_login_customer();
        }

        return MjmRestful_Response::create('ok', $this->get_customer_data($customer), 'Success');
    }

    protected function get_customer_data($customer, $data=NULL){
        if(!is_a($customer,'Shop_Customer')){
          throw new Exception('get_customer_data');
        }

        if(!$data){
        $data =new stdClass();
        }

        $return_fields = $this->get_customer_fields_read();
        foreach($return_fields  as $field){
            if(is_object($customer->$field)){
            $data->$field = empty($customer->$field->name) ? null : $customer->$field->name;
            } else {
            $data->$field = empty($customer->$field) ? null : $customer->$field;
            }
        }
    return  $data;
    }

    public function try_login_customer(){

        $validation = new Phpr_Validation();
        $redirect = null;

        $input_stream = MjmRestful_Helper::get_input_stream();
        $email = MjmRestful_Helper::get_post_json('email',$input_stream);
        $password = MjmRestful_Helper::get_post_json('password',$input_stream);


        if(!empty($email) && !empty($password)){
            try{
            Phpr::$frontend_security->login($validation, $redirect, $email, $password);
            $customer = Phpr::$frontend_security->authorize_user();

                if(!$customer){
                return MjmRestful_Response::create('unauthorised', null, 'Login failed'); //failed login
                }

            $token = $this->renew_token($customer);
            $data = new stdClass();
            $data->access_token = $token;
            $data = $this->get_customer_data($customer, $data);

            return MjmRestful_Response::create('ok', $data, 'Login Successful');
            } catch(Exception $e){
            return MjmRestful_Response::create('unauthorised', null, $e->getMessage()); //failed login
            }
        }
        return MjmRestful_Response::create('unauthorised', null, 'Not logged in'); //failed login
    }

    public function logout_customer(){


        try{
            $customer = Phpr::$frontend_security->authorize_user();

            if($customer){
                //remove api key
                MjmRestful_ApiAccess::remove_device_keys($customer, self::get_users_device_id());
            }

            //kill session
            Phpr::$frontend_security->logout(null);

            return MjmRestful_Response::create('ok',null, 'Logged Out');
        } catch(Exception $e){
            return MjmRestful_Response::create('internal_error', null, $e->getMessage()); //failed logout
        }
    }


    public static function get_users_api_token(){
        return MjmRestful_Helper::get_header('X-Lemonstand-Api-Token');

    }

    public static function get_users_device_id(){
       return md5(Phpr::$request->getUserIP().$_SERVER['HTTP_USER_AGENT']);
    }

    protected function renew_token($customer){

        //check if token needs to be renewed
        if(!MjmRestful_ApiAccess::is_valid_key($customer, self::get_users_api_token(),self::get_users_device_id())){
            //we are issuing a new key for a device so delete any old keys for this device.
            MjmRestful_ApiAccess::remove_device_keys($customer, self::get_users_device_id());
            $data['customer'] = $customer;
            $data['token'] = $this->get_token();
            $data['device_id'] = self::get_users_device_id();
            $access = MjmRestful_ApiAccess::create();
            $access->save($data);
            return $access->token;
        }

    //current token still valid so return
    return self::get_users_api_token();
    }

    protected function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    protected function get_token($length=32){
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        for($i=0;$i<$length;$i++){
            $token .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
        }
        $token .= md5(date('Ymdhis')); //what are the odds?
        return $token;
    }

}