<?php

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
