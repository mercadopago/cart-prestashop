<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mercadopago
 *
 * @author André Fuhrmans
 */
class mercadopago {
    //put your code here
    
     
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

?>
