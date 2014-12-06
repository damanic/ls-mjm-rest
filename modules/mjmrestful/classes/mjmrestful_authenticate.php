<?php

interface MjmRestful_Auth{
    public function is_user_authenticated();
}

abstract class MjmRestful_Authenticate implements MjmRestful_Auth {
    public function is_user_authenticated(){
    }
}