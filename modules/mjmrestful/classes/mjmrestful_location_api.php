<?php
class MjmRestful_Location_API{
protected $fields_allow_read;
protected $fields_allow_write;

    public function __construct(MjmRestful_Router $router){

         //Default Read Fields
        $this->allow_country_fields_read(array('id','name','code_3','code','code_iso_numeric'));
        $this->allow_country_fields_write(array());

        //GET
        //Get countries
        $callback = array(&$this, 'get_countries');
        $options = array('requires_authentication'=>false);
        $route = $router->get('/location/countries/:id/', $callback, $options);
        
    }



    public function allow_country_fields_write(Array $fields){
        $this->fields_allow_write = $fields;
    }

    public function allow_country_fields_read(Array $fields){
        $this->fields_allow_read = $fields;
    }

    public function get_country_fields_read(){
        return $this->fields_allow_read;
    }

    public function get_country_fields_write(){
        return $this->fields_allow_write;
    }

    public function get_countries($route){

        try{
        $data = array();
        $input_stream = MjmRestful_Helper::get_input_stream();
        $id = $route->get_url_param('id');

            $obj = new stdClass();
            $obj->id = $id;


            if(is_numeric($id)){
                $country = Shop_Country::create()->where('id = :id',array('id' => $id))->find();
                $entry = $this->export_country_as_array($country);
                    if($entry){
                        $data = $entry;
                    }
            } else {
                $obj->fetch = 'many';
                $countries = Shop_Country::create()->where('enabled = 1')->ORDER('name ASC')->find_all();
                foreach($countries as $country){
                    $entry = $this->export_country_as_array($country);
                    $data[]= $entry;
                }
            }

            return MjmRestful_Response::create('ok', $data);
        } catch (Exception $e){
            return MjmRestful_Response::create('bad_request', null, $e->getMessage());
        }
    }

    

    public function export_country_as_array($country){
        if(!$country || !is_numeric($country->id)){
            return false;
        }

        $data = array();
        //we use the country ID for REST exchanges.
        $data['id'] = $country->id;

        //default read fields
        foreach($this->get_country_fields_read() as $field){
            if(is_a($country->$field,'Phpr_DateTime')){
                $country->$field = MjmRestful_Helper::utc_timecode($country->$field);
            }
        $data[$field] = $country->$field;
        }

    return $data;
    }




}