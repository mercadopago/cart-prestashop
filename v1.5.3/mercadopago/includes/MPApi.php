<?php

include_once "mercadopago.php";

class MPApi{

    public function getCountries() {    
        $response = MPRestClient::get("/sites");
        $response = $response['response'];
        
        return $response;
    }  
    
    public function getPaymentMethods($country_id){
        $response = MPRestClient::get("/sites/$country_id/payment_methods");
        $response = $response['response'];
        
        return $response;
    }
    
    public function getCategories(){
        $response = MPRestClient::get("/item_categories");
        $response = $response['response'];
        return $response;
    }
}