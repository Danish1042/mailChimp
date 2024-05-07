<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MailChimpController extends Controller
{
    public function index()
    {
        // testing
        return view('welcome');
    }

    public function mailchimp(Request $request)
    {
        $error = Validator::make($request->all(), [
            'email' => 'required|max:100',
        ]);

        if ($error->fails()) {
            return redirect()->back()->withErrors($error)->withInput();
        }
        // MailChimp

        $mailchimp = new \MailchimpMarketing\ApiClient();
        $mailchimp->setConfig([
            'apiKey' => env('MAILCHIMP_APIKEY'),
            'server' => env('MAILCHIMP_SERVER_PREFIX')
        ]);

        $response = $mailchimp->ping->get();

        try {
            $response = $mailchimp->lists->addListMember('808b000d6e', [
                "email_address" => $request->email,
                'merge_fields'  => ['FNAME' => "SHAHEER"],
                "status" => "subscribed",
            ]);
            // dd($response);
          } catch(\Exception $e)
          {
              if(method_exists($e, 'getResponse'))
              {
                $errorResponse = $e->getResponse();
                $jsonDecodedObj = json_decode($errorResponse->getBody()->getContents());
                // the error response from mailchimp should contain a detail property, which describes the error
                if(property_exists($jsonDecodedObj, 'detail'))
                {
                    $errorMsg = $jsonDecodedObj->detail;
                    return back()->with('error' , $errorMsg);
                }
                else
                {
                    return back()->with('error' , "Something went wrong while subscribing");
                }
              }
              else
              {
                return back()->with('error' , "Something went wrong");
              }

            return back()->with('error','Something went wrong');
          }

          return back()->with('success','You Have Successfully Subscribed');
    }
}
