<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories;

/**
 * Description of GeneralRepository
 *
 * @author nicolas
 */
class GeneralRepository extends BaseRepository 
{
    
    public function __construct(\Illuminate\Container\Container $app) 
    {
        parent::__construct($app);
    }
    
    
    //put your code here
    public function getModel()
    {
        return null;
    }
    
    
    
    

}
