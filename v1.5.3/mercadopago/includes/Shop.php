<?php

/** * * NOTICE OF LICENSE * * This source file is subject to the Open Software License (OSL). 
 *  It is also available through the world-wide-web at this URL: *
 *  http://opensource.org/licenses/osl-3.0.php * 
 *  @category    Payment Gateway * @package    	MercadoPago 
 *  @author      André Fuhrman (andrefuhrman@gmail.com) 
 *  @copyright  Copyright (c) MercadoPago [http://www.mercadopago.com] 
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */
class Basic {
      
     
        public     $accesstoken;
        protected  $client_id;
        protected  $client_secret;
        public     $error;
        protected  $date;
        protected  $expired;


        
       ///// function just to debug the code if is needed
        
       static function debug($error){
               echo ('<pre>');
               print_r($error);
               echo ('</pre>');
       } 
        
       
         ///// function to post the datas
         public function DoPost($fields,$url,$heads,$codeexpect,$type,$method){
                    
                    // buld the post data follwing the api needs
                    if($type == 'json'){
                    $posts = json_encode($fields);
                    } else if ($type == 'none') {
                    $posts = $fields;
                    } else {
                    $posts = http_build_query($fields);    
                    }
                  
                    // change the curl method follwing the api needs
                    switch ($method):
                    case 'get':
                    $options = array(
                               CURLOPT_RETURNTRANSFER => '1',
                               CURLOPT_HTTPHEADER => $heads,
                               CURLOPT_SSL_VERIFYPEER => 'false',
                               CURLOPT_URL => $url,
                               CURLOPT_POSTFIELDS => $posts ,
                               CURLOPT_CUSTOMREQUEST => "GET"
                            );
                    break;
                    case 'put':
                      $options = array(
                                CURLOPT_RETURNTRANSFER => 1,
                                CURLOPT_HTTPHEADER => $heads,
                                CURLOPT_SSL_VERIFYPEER => 'false',
                                CURLOPT_URL => $url,
                                CURLOPT_POSTFIELDS => $posts,    
                                CURLOPT_CUSTOMREQUEST => "PUT",
                                CURLOPT_HEADER => 1
                             );  
                    break;
                    case 'post':
                         $options = array(
                                CURLOPT_RETURNTRANSFER => '1',
                                CURLOPT_HTTPHEADER => $heads,
                                CURLOPT_SSL_VERIFYPEER => 'false',
                                CURLOPT_URL => $url,
                                CURLOPT_POSTFIELDS => $posts,    
                                CURLOPT_CUSTOMREQUEST => "POST",
                             ); 
                    break;
                    case 'delete':
                        $options = array(
                                CURLOPT_RETURNTRANSFER => '1',
                                CURLOPT_HTTPHEADER => $heads,
                                CURLOPT_SSL_VERIFYPEER => 'false',
                                CURLOPT_URL => $url,
                                CURLOPT_POSTFIELDS => $posts,    
                                CURLOPT_CUSTOMREQUEST => "DELETE",
                             ); 
                        
                    break;      
                    default:
                            $options = array(
                               CURLOPT_RETURNTRANSFER => '1',
                               CURLOPT_HTTPHEADER => $heads,
                               CURLOPT_SSL_VERIFYPEER => 'false',
                               CURLOPT_URL => $url,
                               CURLOPT_POSTFIELDS => $posts ,
                               CURLOPT_CUSTOMREQUEST => "GET"
                            );
                    break;
                    endswitch;
  
                // do a curl call
                $call = curl_init();
                curl_setopt_array($call,$options);
                // execute the curl call
                $dados = curl_exec($call);
                // get the curl statys
                $status = curl_getinfo($call);
                // close the call
                curl_close($call);
                // check to see if the call was succesful 
                if ($status['http_code'] != $codeexpect){
                $this->debug($dados);
              //  $this->debug($status);
                return false;
                } else {
               // change the json retur to a php array and return it
                return json_decode($dados,true);        
                } 
        
        }
        
        public function getAccessToken(){
         
            $data = getdate();
            $time = $data[0];
             
     
            // verifica se já existe accesstoken valido, caso exista, retorna o accesstoken
            if(isset($this->accesstoken) && isset($this->date)){          
                $timedifference = $time - $this->date;
                if($timedifference < $this->expired){
                return $this->accesstoken;
                }
           }
            // get the clients variables
                $post = array(
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'grant_type' => 'client_credentials'
                 );
                // set the header
                $header = array('Accept: application/json','Content-Type: application/x-www-form-urlencoded');
                // set the url to get the access token
                $url = 'https://api.mercadolibre.com/oauth/token';
                // call the post function. expection 200 as return
                $dados = $this->DoPost($post,$url,$header,'200','post','post');
                // set the access token
                $this->accesstoken = $dados['access_token'];
                 // guarta o hoarario, prazo de expiração e returna o access token
                $this->date = $time;
                $this->expired = $dados['expires_in'];
                return $dados['access_token'];
       }
     
       
    
}
Class Mpublic extends Basic {
    
     public function getCountries() {
            
        $url = 'https://api.mercadolibre.com/sites/';
        $header = array('Accept: application/json');
        $countries = $this->DoPost(null,$url,$header,'200','none','get');
	return $countries;
    }  
       
     public function GetMethods($country_id){
       
        $url = "https://api.mercadolibre.com/sites/" . $country_id .  "/payment_methods";
        $header = array('Accept: application/json');  
        $methods = $this->DoPost(null,$url,$header,'200','none','get');
        return $methods;
    
     }
}

Class MPShop extends Basic {


       // do the client authentication
    public function __construct($client,$secret){
   
        $this->client_id = $client;
        $this->client_secret = $secret;
    
     }
       
    

      // Generate the botton
      public function GetCheckout($data,$excludes){
       
            if($excludes != ''){
                
                 $methods_excludes = preg_split("/[\s,]+/", $excludes); 
                 foreach ($methods_excludes as $exclude ){
                 $excludemethods[] = array('id' => $exclude);     
                 }
                
                $opt = array(
                "external_reference" => $data['external_reference'],
                "items" => array(
                    array ("id" => $data['external_reference'], // updated
                    "title" => $data['title'],
                    "description" => $data['quantity'] . ' x ' . $data['title'],
                    "quantity" => 1,
                    "unit_price" => round($data['amount'], 2),
                    "currency_id" => $data['currency'],
                    "picture_url"=> $data['image'],
                    )),
                    "payer" => array(
                     "name" => $data['payment_firstname'],
                     "surname" => $data['payment_lastname'],
                     "email" => $data['email']
                    ),
                   "back_urls" => array(
                   "pending" => $data['pending'],
                   "success" => $data['approved']
                   ),           
                   "payment_methods" => array(
                   "excluded_payment_methods" => $excludemethods
                   )
                );
            }else{
                $opt = array(
                "external_reference" => $data['external_reference'],
                "items" => array(
                    array ("id" => $data['external_reference'], // updated
                    "title" => $data['title'],
                    "description" => $data['quantity'] . ' x ' . $data['title'],
                    "quantity" => 1,
                    "unit_price" => round($data['amount'], 2),
                    "currency_id" => $data['currency'],
                    "picture_url"=> $data['image'],
                    )),
                    "payer" => array(
                     "name" => $data['payment_firstname'],
                     "surname" => $data['payment_lastname'],
                     "email" => $data['email']
                    ),
                   "back_urls" => array(
                   "pending" => $data['pending'],
                   "success" => $data['approved']
                   ),  
                );
                
            } 
            $this->getAccessToken(); 
            $url = 'https://api.mercadolibre.com/checkout/preferences?access_token=' . $this->accesstoken;
            $header = array('Content-Type:application/json', 'User-Agent:MercadoPago PrestaShop-1.5.3 Cart v1.0.0', 'Accept: application/json');
            $dados = $this->DoPost($opt,$url,$header,'201','json','post');
            $link = $dados['init_point'];
            $bt = '<a href="'.$link.'" name="MP-payButton" class="blue-l-rn-ar">Comprar</a>
            <script type="text/javascript" src="https://www.mercadopago.com/org-img/jsapi/mptools/buttons/render.js"></script>';
            return $bt;
      }
      
           

      public function GetStatus($id){
          
            $this->getAccessToken(); 
            $url = "https://api.mercadolibre.com/collections/notifications/" . $id . "?access_token=" . $this->accesstoken;
            $header = array('Accept: application/json', 'Content-Type: application/x-www-form-urlencoded');
            $retorno = $this->DoPost($opt=null,$url,$header,'200','none','post');
            return $retorno;
                   
      }
      

}  
?>

