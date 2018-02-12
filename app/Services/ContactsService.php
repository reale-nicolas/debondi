<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services;

use App\Repositories\ContactsRepository;

/**
 * Description of ContactsService
 *
 * @author nicolas
 */
class ContactsService extends BaseService
{
    protected $contactsRepository;
    
    
    public function __construct(ContactsRepository $contact) 
    {
        $this->contactsRepository = $contact;
    }
    
    public function create($data)
    {
        return $this->contactsRepository->create(
            array(
                "message"   => $data->message,
                "subject"   => $data->subject, 
                "email"     => $data->email
            )
        );   
    }
}
