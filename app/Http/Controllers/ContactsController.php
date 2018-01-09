<?php

namespace App\Http\Controllers;

use App\Services\ContactsService;
use Symfony\Component\HttpFoundation\Request;
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
//        echo "<pre>";print_r($request->all());echo "</pre>";
        if ($request->all()) 
        {
            $validator = Validator::make($request->all(), [
                'subject'       => 'required|string',
                'message'       => 'required|string',
                'email'         => 'email',
            ]);

            if ($validator->fails())
            {
                return response()->json([
                    'result'    => 'ERROR',
                    'detail'    => 'Differents errors were found in the input data',
                    'fields'    => $validator->errors()
                ]);
            }
        
            $result = $this->contactService->create(
                    $request->subject, 
                    $request->message,
                    $request->email
            );

            
            return response()->json([
                'result'    => 'SUCCESS',
                'detail'    => '',
                'data'      => ''
            ]);
            
        }
        
        return response()->json([
            'result'    => 'ERROR',
            'detail'    => 'You have to provide differents input data.'
        ]);
    }
}
