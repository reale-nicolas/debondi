<?php

namespace App\Models;

class Constants extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'constants';
    
    
    public static function get($constantName)
    {   
        if($res = static::where('name', $constantName))
        {
            return $res->first()->attributes;
        }
        
        return null;
    }
    
    public static function getValue($constantName)
    {
        if ($result =  Constants::get($constantName))
            return $result['value'];
        
        return null;
    }
}
