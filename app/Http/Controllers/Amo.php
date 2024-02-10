<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use AmoCRM\Client\AmoCRMApiClient;
use Illuminate\Support\Facades\Storage;

class Amo extends Controller
{
    public function authorized(Request $request)
    {
        if(!$request->code)
        {
            return view('welcome',['status','bad']);
        }
        $clientid = env('AMO_CLIENT_ID');
        $clientSecret = env('AMO_CLIENT_SECRET');
        $redirectUri = env('AMO_REDIRECT');
        $accountDomain = env("AMO_ACCOUNT_DOMAIN");
        $amoClient = new AmoCRMApiClient($clientid,$clientSecret,$redirectUri);
        $amoClient->setAccountBaseDomain($accountDomain);
        $token = $amoClient->getAccessTokenByCode($request->code);
        Storage::disk('Local')->put('amo/token.json',$token);
        return view('welcome',['status'=>'good']);
    }
}
