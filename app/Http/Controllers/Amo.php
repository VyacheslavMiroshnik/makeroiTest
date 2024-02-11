<?php

namespace App\Http\Controllers;

use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\EntitiesServices\Leads;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\LeadsFilter;
use AmoCRM\Filters\TasksFilter;
use AmoCRM\Models\CustomFields\NumericCustomFieldModel;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NullCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use Illuminate\Http\Request;
use AmoCRM\Client\AmoCRMApiClient;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use League\OAuth2\Client\Token\AccessToken;
use Psy\Util\Json;

class Amo extends Controller
{
/*
 * Получения токена по коды и его запись в файл
 * */
    public function authorized(Request $request)
    {
        if(!$request->code)
        {
            return view('welcome',['status'=>Storage::disk('local')->get('token.json')]);
        }
        $amoClient = self::init();
        $rowToken = Storage::disk('local')->get('token.json');
          $token = $amoClient->getOAuthClient()->getAccessTokenByCode($request->code);
          self::savetoken($token);
          return response()->json(['success'=>Storage::disk('local')->get('token.json')]);
    }
    /*
    * Получаем данные из формы обрабатываем и передаем на сделку
    * */
    public function leads(Request $request)
    {
        $amoClient = self::init();
        $rowToken = json_decode(Storage::disk('local')->get('token.json'),1);
        $token = new AccessToken($rowToken);
        $amoClient->setAccessToken($token);
        $leadsService = $amoClient->leads();
        $lead = new LeadModel();
        $lead->setCustomFieldsValues(new CustomFieldsValuesCollection());
        $lead = self::createOrUpdateLead($lead,$request);
        try {
            $lead = $leadsService->addOne($lead);
        } catch (AmoCRMApiException $e) {
            echo $e->getMessage();
            die;
        }
        return view('welcome',['response'=>'ok']);
    }
    private function savetoken($token):void
    {
        Storage::disk('local')->put('token.json',json_encode($token->jsonSerialize(),JSON_PRETTY_PRINT));
    }
    /*
   * Шаблон  для создания клиента
   * */
    private function init():AmoCRMApiClient
    {
        $clientid = env('AMO_CLIENT_ID');
        $clientSecret = env('AMO_CLIENT_SECRET');
        $redirectUri = env('AMO_REDIRECT');
        $accountDomain = env("AMO_ACCOUNT_DOMAIN");
        $amoClient = new AmoCRMApiClient($clientid, $clientSecret, $redirectUri);
        $amoClient->setAccountBaseDomain($accountDomain);
        return $amoClient;
    }
    /*
   * Шаблон  для создания сделки
   * */
    private function createLead(LeadModel $lead, $request)
    {
        $nameLead = $request->lead ??'test';
        $price  = +$request->price ?? 0;
        $costPrice  = +$request->costPrice ?? 0;
        $profit = $price-$costPrice;
        $costFieldId = 62291;
        $profitFieldId = 62341;
        $leadCustomFieldsValues = $lead->getCustomFieldsValues();
        $numericFirstField = new NumericCustomFieldValuesModel();
        $numericFirstField->setFieldId($costFieldId);
        $numericFirstField->setValues(
            (new NumericCustomFieldValueCollection())
                ->add((new NumericCustomFieldValueModel())->setValue($costPrice))
        );
        $numericSecondField = new NumericCustomFieldValuesModel();
        $numericSecondField->setFieldId($profitFieldId);
        $numericSecondField->setValues(
            (new NumericCustomFieldValueCollection())
                ->add((new NumericCustomFieldValueModel())->setValue($profit))
        );
        $leadCustomFieldsValues->add($numericFirstField);
        $leadCustomFieldsValues->add($numericSecondField);
        $lead->setCustomFieldsValues($leadCustomFieldsValues);
        $lead->setName($nameLead);
        $lead->setPrice($price);
        return $lead;
    }
    function update(Request $request)
    {
        $leadId= $request->leadId;
        $amoClient = self::init();
        $rowToken = json_decode(Storage::disk('local')->get('token.json'), 1);
        $token = new AccessToken($rowToken);
        $amoClient->setAccessToken($token);
        $leadsService = $amoClient->leads();
            //Получим сделки по фильтру и с полем with=is_price_modified_by_robot,loss_reason,contacts
        try {
            $lead = $leadsService->getOne($leadId,[])->toArray();
        } catch (AmoCRMApiException $e) {
            return view('welcome',['error'=>'Нет такой сделки']);
        }
        $price = $lead['price'];
        $name = $lead['name'];
        $costPrice = $lead['custom_fields_values'][0]['values'][0]['value'];
                return view('welcome',['lead'=>['price'=>$price,'name'=>$name,'costPrice'=>$costPrice,'id'=>$leadId]]);
  }
  function updateLead(Request $request)
  {
      $leadId = +$request->leadId;
      $amoClient = self::init();
      $rowToken = json_decode(Storage::disk('local')->get('token.json'), 1);
      $token = new AccessToken($rowToken);
      $amoClient->setAccessToken($token);
      $leadsService = $amoClient->leads();
      $costFieldId = 62291;
      $profitFieldId = 62341;
      $nameLead = $request->lead ??'test';
      $price  = +$request->price ?? 0;
      $costPrice  = +$request->costPrice ?? 0;
      $profit = $price-$costPrice;
      $lead = $leadsService->getOne($leadId, []);
      $customFields = $lead->getCustomFieldsValues();
      $lead->setName($nameLead);
      $lead->setPrice($price);
      foreach ($customFields as $customFieldValues) {
           switch ($customFieldValues->getFieldId())
           {
               case $costFieldId:
                   $customFieldValue = $customFieldValues->getValues()->first();
                   $customFieldValue->setValue($costPrice);
                   break;

               case $profitFieldId:
                   $customFieldValue = $customFieldValues->getValues()->first();
                   $customFieldValue->setValue($profit);
                   break;
           }
      }
      $lead->setCustomFieldsValues($customFields);

      try {
          $amoClient->leads()->updateOne($lead);
      } catch (AmoCRMApiException $e) {
          printError($e);
          die;
      }
      return view('welcome', ['response' => 'ok']);
  }
}
