<?php

namespace App\Repositories;

use App\Interfaces\RepositoryInterface;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class BaseRepository
 */
abstract class BaseRepository implements RepositoryInterface
{    
    /**
     * @var App
     */
    private $app;

    /**
     * @var
     */
    protected $model;

    
    public function model(){
        return $this->model;
    }
    /**
     * @param App $app
     * @throws \Bosnadev\Repositories\Exceptions\RepositoryException
     */
    public function __construct(App $app) {
        $this->app = $app;
        $this->makeModel();
    }
    
    
    /**
     * Specify Model class name
     * 
     * @return mixed
     */
    abstract public function getModel();
    
    
    /**
     * @return Model
     * @throws RepositoryException
     */
    public function makeModel() 
    {
        $model = $this->app->make($this->getModel());

        if (!$model instanceof Model)
            throw new Exception("Class {$this->getModel()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
    }
    
    
    /**
     * @param array $columns
     * @return mixed
     */
    public function all($columns = array('*')) {
        return $this->model->get($columns);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = array('*')) {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data) {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute="id") {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id) {
        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*')) {
        return $this->model->find($id, $columns);
    }

    /**
     * @param array $attribute
     * @param array $value
     * @param array $columns
     * 
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*')) {
        $repo = $this->model;
        foreach ($attribute as $k => $name) 
        {
            $repo = $repo->where($name, $value[$name]);
        }
        return $repo->get();
    }
    
    
    public function getRouteOptions($latFrom, $lngFrom, $latTo, $lngTo, $maxDistance = 0.8)
    {
        $result = DB::select(
            'call sp_get_nearly_stops_origin_destiny(?,?,?,?,?)', 
            array(
                $latFrom, 
                $lngFrom, 
                $latTo, 
                $lngTo, 
                $maxDistance
            )
        ); 
        
        return $result;
    }
    
}
