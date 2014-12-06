<?php
class MjmRestfulApiExample_V1{
protected $field_access;

    function __construct(){

        /* This class contains simulated functions for testing.
         *
         * There is a reserved path for authentication login
         * /authenticate/login
         *
         * Accepts
         * post: username , post: password
         *
         * Returns: token
         *
         * The returned token can be stored in your application.
         * and sent in a header
         */

        $this->field_access['Shop_Customer']['read'] = array('id','first_name','last_name','email','company','phone');
        $this->field_access['Shop_Customer']['write'] = array('first_name','last_name','email','company','phone');

    }
    public function get_Customer($route){

        $customer = Phpr::$frontend_security->authorize_user();
            if(!$customer){
                return MjmRestful_Response::create('unauthorised', null, 'Cannot Get Customer Data. Login Required'); //failed login
            }

        $data = new stdClass();
            foreach($this->field_access['Shop_Customer']['read'] as $field){
            $data->$field = $customer->$field;
            }

        return MjmRestful_Response::create('ok',$data, 'customer served');
    }





    //get
    public function get_ProductReview($route) {
        //url parameters specified in the route can be fetched by name.
        //get :id
        $id = $route->get_url_param('id');

        if(is_numeric($id)){
            //fetch the review and populate data object.
            $data = new stdClass();
            $data->id = $id ;
            $data->title = 'My Review Title';
            $data->content = 'Good stuff';
            $data->stars = 5;

            if($data){
                //return data with ok header.
                return MjmRestful_Response::create('ok',$data, 'your product has been served');
            } else{
                //no data to return but still ok header.
                return MjmRestful_Response::create('ok',null, 'no product available for id '.$id);
            }
        }
        //clients request was flawed, let them know with bad_request header
        return MjmRestful_Response::create('bad_request', null, 'invalid value given for :id');
    }

    //create
    public function create_ProductReview($route){
        //create a new review with post data
        $data = new stdClass();
        $data->id = 'createdidnumber';
        $data->title = post('title');
        $data->content = post('content');
        $data->stars = post('stars');

        //return ok if saved
        return MjmRestful_Response::create('ok',$data, 'created review with following post data');

        //or return other headers if issues
    }

    //update
    public function update_ProductReview($route){
        $id = $route->get_url_param('id');

        if(is_numeric($id)){
            //update the review
            $data = new stdClass();
            $data->id = $id;
            $data->title = post('title');
            $data->content = post('content');
            $data->stars = post('stars');
            return MjmRestful_Response::create('ok',$data, 'updated review'. $id .'with following post data');
        }
        return MjmRestful_Response::create('bad_request', null, 'invalid value given for :id');
    }


    //delete
    public function delete_ProductReview($route){
        $id = $route->get_url_param('id');
        if(is_numeric($id)){
            //delete the review
            return MjmRestful_Response::create('ok', null, 'deleted review'. $id);
        }
        return MjmRestful_Response::create('bad_request', null, 'invalid value given for :id');
    }

}