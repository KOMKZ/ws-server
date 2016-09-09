<?php
namespace wsserver\base;

interface IdentityInterface{
    public static function getIdentity();
    public static function findIdentity($id);
    
}
