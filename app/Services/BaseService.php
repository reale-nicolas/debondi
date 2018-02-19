<?php
namespace App\Services;

use App\Repositories\GeneralRepository;

/**
 * Description of BaseService
 *
 * @author nicolas
 */
class BaseService 
{
    public $generalRepository;
    
    public function __construct(GeneralRepository $repository) 
    {
        $this->generalRepository = $repository;
    }
}
