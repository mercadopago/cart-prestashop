<?php
include_once('../../../config/config.inc.php');
include_once('Shop.php');

     
        if (isset($_REQUEST['id'])) {

        $id = $_REQUEST['id'];

        $client_id = Db::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = 'mercadopago_CLIENT_ID'");
        $client_secret = Db::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = 'mercadopago_CLIENT_SECRET'");
  
        $checkdata = New MPShop($client_id['value'],$client_secret['value']);

        $dados = $checkdata->GetStatus($id);
        
        $order_id = $dados['collection']['external_reference'];
        $order_status = $dados["collection"]["status"];


        switch ($order_status) {
                case 'approved':
                 $nomestatus = "mercadopago_STATUS_1";
                 break;
                 case 'pending':
                 $nomestatus = "mercadopago_STATUS_0";
                 break;    
                 case 'in_process':
                 $nomestatus = "mercadopago_STATUS_0";   
                 break;    
                 case 'reject':
                 $nomestatus = "mercadopago_STATUS_2"; 
                 break;    
                 case 'refunded':
                 $nomestatus = "mercadopago_STATUS_2";
                 break;    
                 case 'cancelled':
                 $nomestatus = "mercadopago_STATUS_2";     
                 break;    
                 case 'in_metiation':
                 $nomestatus = "mercadopago_STATUS_0";
                 break;
                      
        }
        
          // Get Id StatusDb::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = 'mercadopago_CLIENT_ID'");
		$result = Db::getInstance()->getRow("SELECT value FROM "._DB_PREFIX_."configuration WHERE name = '".$nomestatus."'");
		$state = $result['value'];
           
           // �Update order
            Db::getInstance()->ExecuteS("INSERT INTO "._DB_PREFIX_."order_history (`id_employee`, `id_order`, `id_order_state`, `date_add`) VALUES ('0', '".$order_id."', '". $state . "', NOW())");


            // Send email
           
		$extraVars = array();
		$history = new OrderHistory();
		$history->id_order = intval($order_id);
		$history->changeIdOrderState(intval($state),intval($order_id));
		$history->addWithemail(true,$extraVars);
        
        
}  
        
        


?>
    
