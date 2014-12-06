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
        $todays_date = new Phpr_DateTime();
        $this->token_expire_date = $todays_date->addDays(365);
    }

    public static function is_valid_key(Shop_Customer $customer, $token, $device_id){
            $obj = MjmRestful_ApiAccess::create()->where('customer_id = :customer_id AND token = :token AND device_id = :device_id AND token_expire_date > NOW()',
                                                    array('customer_id'=>$customer->id, 'token'=>$token,'device_id'=>$device_id))->find_all();
        if($obj->id){
            return true;
        }
    return false;
    }

    public static function remove_device_keys(Shop_Customer $customer, $device_id){
        $obj = MjmRestful_ApiAccess::create()->where('customer_id = :customer_id AND device_id = :device_id',
            array('customer_id'=>$customer->id, 'device_id'=>$device_id))->find_all();
        if($obj){
            foreach($obj as $old_key){
            $old_key->delete();
            }
            return true;
        }
        return true;
    }

    public static function get_user_by_token($token, $device_id){
        $obj = MjmRestful_ApiAccess::create()->where('token = :token AND device_id = :device_id AND token_expire_date > NOW()',
            array('token'=>$token,'device_id'=>$device_id))->find_all();

        if($obj){
            return $obj->customer;
        }
    return false;
    }


}
