<?php

namespace App\Http\Controllers;

use App\AStar\AStarServices;
use App\Services\BusService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use function response;


class BusLinesController extends Controller
{
    protected $busService;
    protected $aStarService;
    
    protected $availableFieldsToSearchByForLines = [
       "line", "ramal", "zone"
    ];
    protected $availableFieldsToSearchByForStops = [
       "name", "latitude", "longitude"
    ];
    
    public function __construct(BusService $busService, AStarServices $aStar) 
    {
        $this->busService = $busService;
        $this->aStarService = $aStar;
    }
    
    
    public function getRoute(Request $request)
    {
        if ($request->all()) 
        {
            $validator = Validator::make($request->all(), [
                'latFrom'       => 'required|numeric',
                'lngFrom'       => 'required|numeric',
                'latTo'         => 'required|numeric',
                'lngTo'         => 'required|numeric',
                'maxDistance'   => ''
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    'result'    => 'ERROR',
                    'detail'    => 'Differents errors were found in the input data',
                    'fields'    => $validator->errors()
                ]);
            }
        
            $result = $this->busService->getRoute(
                    $request->latFrom, 
                    $request->lngFrom,
                    $request->latTo, 
                    $request->lngTo,
                    $request->maxDistance
            );

            
            return response()->json([
                'result'    => 'SUCCESS',
                'detail'    => '',
                'data'      => $result
            ]);
            
        }
        
        return response()->json([
            'result'    => 'ERROR',
            'detail'    => 'You have to provide differents input data.'
        ]);
    }
    
    
    
    /**
     * Return a specific line detail specified by id
     * 
     * @param int $id
     * 
     * @return Response
     */
    public function getLineById($id)
    {
        $validator = Validator::make(array('id' => $id), [
            'id'      => 'required|integer'
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'result'    => 'ERROR',
                'detail'    => 'Differents errors were found in the input data',
                'fields'    => $validator->errors()
            ]);
        }
        
        $line = $this->busService->getLineById($id);
        
        if (!$line || !$line->count()) {
            return response()->json([
                'result'    => 'SUCCESS',
                'detail'    => 'We couldn\'t find records with your criteria',
                'dataset'   => '0',
                'data'      => null
            ]);
        }
        
        return response()->json([
            'result'    => 'SUCCESS',
            'detail'    => '',
            'dataset'   => '1',
            'data'      => $line
        ]);
    }
    
    
    /**
     * Return all lines detail
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function getLines(Request $request)
    {
        if ($request->all()) 
        {
            $validator = Validator::make($request->all(), [
                'line'      => 'integer',
                'ramal'     => 'string',
                'zone'      => 'string',
                'detailed'  => 'boolean'
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    'result'    => 'ERROR',
                    'detail'    => 'Differents errors were found in the input data',
                    'fields'    => $validator->errors()
                ]);
            }

            if (array_intersect(array_keys($request->all()), $this->availableFieldsToSearchByForLines)) {
                $arrColumnsFilters = array_intersect(array_keys($request->all()), array_values($this->availableFieldsToSearchByForLines));
                $arrValuesFilters  = array_intersect_key($request->all(), array_flip($arrColumnsFilters));
            
                $result = $this->busService->getLinesBy($arrColumnsFilters, $arrValuesFilters, $request->detailed);
            }
            else 
            {
                $result = $this->busService->getLines($request->detailed);
            }
        } 
        else 
        {
            $result = $this->busService->getLines();
        }
        
        
        if (!$result->count()) 
        {
            return response()->json([
                'result'    => 'SUCCESS',
                'detail'    => 'We couldn\'t find records with your criteria',
                'dataset'   => '0',
                'data'      => null
            ]);
        }
        
        return response()->json([
            'result'    => 'SUCCESS',
            'detail'    => '',
            'dataset'   => $result->count(),
            'data'      => $result
        ]);
    }
    
    
    public function getBusStopsNearby(Request $request)
    {
//        if ($request->all()) 
//        {
//            $validator = Validator::make($request->all(), [
//                'latitude'   => 'required|numeric',
//                'longitude'  => 'required|numeric',
//                'radio'      => 'required|integer'
//            ]);
//
//            if ($validator->fails())
//            {
//                return response()->json([
//                    'result'    => 'ERROR',
//                    'detail'    => 'Differents errors were found in the input data',
//                    'fields'    => $validator->errors()
//                ]);
//            }
        
            $result = $this->aStarService->getBusStopsNearby($request->latitude, $request->longitude, $request->radio);
            var_dump($result);
//            if (!$result->count()) 
//            {
//                return response()->json([
//                    'result'    => 'SUCCESS',
//                    'detail'    => 'We couldn\'t find records with your criteria',
//                    'dataset'   => '0',
//                    'data'      => null
//                ]);
//            }
//
//            return response()->json([
//                'result'    => 'SUCCESS',
//                'detail'    => '',
//                'dataset'   => $result->count(),
//                'data'      => $result
//            ]);
//            
//        }
//        
//        return response()->json([
//            'result'    => 'ERROR',
//            'detail'    => 'You have to provide differents input data.'
//        ]);
    }
    
    
    /**
     * Return a specific bus stop specified by id
     * 
     * @param int $id
     * 
     * @return Response
     */
    public function getStopById($id)
    {
        $validator = Validator::make(array('id' => $id), [
            'id'      => 'required|integer'
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'result'    => 'ERROR',
                'detail'    => 'Differents errors were found in the input data',
                'fields'    => $validator->errors()
            ]);
        }
        
        $busStop = $this->busService->getStopById($id);
        
        if (!$busStop || !$busStop->count()) {
            return response()->json([
                'result'    => 'SUCCESS',
                'detail'    => 'We couldn\'t find records with your criteria',
                'dataset'   => '0',
                'data'      => null
            ]);
        }
        
        return response()->json([
            'result'    => 'SUCCESS',
            'detail'    => '',
            'dataset'   => '1',
            'data'      => $busStop
        ]);
    }
    
    
    /**
     * Return all bus stops 
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function getStops(Request $request)
    {
        if ($request->all() && array_intersect(array_keys($request->all()), $this->availableFieldsToSearchByForStops)) 
        {
            $validator = Validator::make($request->all(), [
                'name'      => 'string',
                'latitude'  => 'numeric',
                'longitude' => 'numeric'
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    'result'    => 'ERROR',
                    'detail'    => 'Differents errors were found in the input data',
                    'fields'    => $validator->errors()
                ]);
            }

            $arrColumnsFilters = array_intersect(array_keys($request->all()), array_values($this->availableFieldsToSearchByForStops));
            $arrValuesFilters  = array_intersect_key($request->all(), array_flip($arrColumnsFilters));
            
            $result = $this->busService->getStopsBy($arrColumnsFilters, $arrValuesFilters);
        } 
        else 
        {
            $result = $this->busService->getStops();
        }
        
        
        if (!$result->count()) 
        {
            return response()->json([
                'result'    => 'SUCCESS',
                'detail'    => 'We couldn\'t find records with your criteria',
                'dataset'   => '0',
                'data'      => null
            ]);
        }
        
        return response()->json([
            'result'    => 'SUCCESS',
            'detail'    => '',
            'dataset'   => $result->count(),
            'data'      => $result
        ]);
    }
}
