<?php

class MjmRestful_ApiAccess extends Db_ActiveRecord
{
    public $table_name = 'mjmrestful_apiaccess';

    public $belongs_to = array(
        'customer'=>array('class_name'=>'Shop_Customer', 'foreign_key'=>'customer_id'),
    );

    protected $api_added_columns = array();

    public static function create(){
        return new self();
    }

    public function define_columns($context = null){
        $this->define_column('token', 'API Authorisation Token')->validation()->unique('Obviously Unique');
        $this->define_column('device_id', 'Device Identification');
        $this->define_column('token_expire_date', 'Date Token Expires');
        $this->define_relation_column('customer', 'customer', 'Customer', db_varchar, "concat(@first_name, ' ', @last_name, ' (', @email, ')')")->defaultInvisible();
    }

    public function before_create($deferred_session_key = null){
        $api_settings = MjmRestful_SettingsManager::get();
        $expire_days = is_numeric($api_settings->token_expire) ? $api_settings->token_expire : 365;
        $todays_date = new Phpr_DateTime();
        $this->token_expire_date = $todays_date->addDays($expire_days);
    }

    public static function is_valid_key(Shop_Customer $customer, $token, $device_id=false){
        $where = "customer_id = :customer_id AND token = :token AND token_expire_date > NOW()";

        if($device_id){
            $where .= ' AND device_id = :device_id';
        }

        $obj = MjmRestful_ApiAccess::create()->where($where, array('customer_id'=>$customer->id,'token'=>$token,'device_id'=>$device_id))->find_all();

        if($obj->id){
            return true;
        }

    return false;
    }

    public static function remove_device_keys(Shop_Customer $customer, $device_id=false){
        $where = 'customer_id = :customer_id';

        if($device_id){
            $where .= ' AND device_id = :device_id';
        }

        $obj = MjmRestful_ApiAccess::create()->where($where, array('customer_id'=>$customer->id, 'device_id'=>$device_id))->find_all();
        if($obj){
            foreach($obj as $old_key){
            $old_key->delete();
            }
            return true;
        }
        return true;
    }

    public static function get_user_by_token($token, $device_id=false){

        $where = 'token = :token AND token_expire_date > NOW()';
        if($device_id){
            $where .= ' AND device_id = :device_id ';
        }
        $obj = MjmRestful_ApiAccess::create()->where($where,
            array('token'=>$token,'device_id'=>$device_id))->find_all();

        if($obj){
            return $obj->customer;
        }
    return false;
    }

}
