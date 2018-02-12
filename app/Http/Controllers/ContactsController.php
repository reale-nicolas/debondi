<?php

namespace App\Http\Controllers;
//use Symfony\Component\HttpFoundation\Request;


use App\Services\ContactsService;
use Illuminate\Http\Request;
use Validator;
use function response;


class ContactsController extends Controller
{
    protected $contactService;
    
    public function __construct(ContactsService $contactService)
    {
        $this->contactService = $contactService;
    }
    
    
    public function create(Request $request)
    {
        
        if ($request->all()) 
        {
            $validator = Validator::make($request->all(), [
                'email'         => 'email|max:50',
                'subject'       => 'required|string|max:255',
                'message'       => 'required|string|max:255'
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    'result'    => 'ERROR',
                    'detail'    => 'Differents errors were found in the input data',
                    'fields'    => $validator->errors()
                ]);
            }
        
            $result = $this->contactService->create($request);
            
            if ($result) {
                return response()->json([
                    'result'    => 'SUCCESS',
                    'detail'    => 'It was imposible to register a new record.',
                    'data'      => ''
                ]);
            } else {
                return response()->json([
                    'result'    => 'ERROR',
                    'detail'    => '',
                    'data'      => ''
                ]);
            }
            
        }
        
        return response()->json([
            'result'    => 'ERROR',
            'detail'    => 'You have to provide differents input data.'
        ]);
    }
}
