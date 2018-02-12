<?php

namespace App\Models;


class Contacts extends BaseModel
{
   
    protected $table = 'contacts';
    
    protected $fillable = [
        'subject',
        'email',
        'message'
    ];
    
    public $timestamps = false;
    
}
