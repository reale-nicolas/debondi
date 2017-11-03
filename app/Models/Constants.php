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
        return static::where('name', $constantName)->first()->attributes;
    }
    
    public static function getValue($constantName)
    {
        $result =  Constants::get($constantName);
        
        return $result['value'];
    }
}
