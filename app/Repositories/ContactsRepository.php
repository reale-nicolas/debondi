<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories;

use Illuminate\Container\Container;

/**
 * Description of ContactsRepository
 *
 * @author nicolas
 */
class ContactsRepository extends BaseRepository
{
    
    public function __construct(Container $app) {
        parent::__construct($app);
    }
    
    
    public function getModel() 
    {
        return 'App\Models\Contacts';
    }
}
