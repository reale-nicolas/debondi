<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BusService;

class BusLinesWebController extends Controller
{
    protected $busService;
    
    protected $availableFieldsToSearchByForLines = [
       "line", "ramal", "zone"
    ];
    protected $availableFieldsToSearchByForStops = [
       "name", "latitude", "longitude"
    ];
    
    public function __construct(BusService $busService) 
    {
        $this->busService = $busService;
    }
    
    
    
    public function showIndex()
    {
        $arrLines = array();
        $lines = $this->busService->getLines();
        
        foreach($lines as $line)
        {
            $arrLines[$line->line][$line->ramal][$line->zone] = $line;
        }
        
        return view('index', ['lines' => $arrLines]);
    }
    
    /**
     * Return all lines detail
     * 
     * @param Request $request
     * 
     * @return Response
     */
    public function getLines()
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
