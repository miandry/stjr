<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:49
 */

namespace Drupal\commande_management;

use Drupal\Core\Datetime\DrupalDateTime; 

class CommandeManagement 
{

       function saveCommandes(){
               // kint(    $arrayData );
            $service = \Drupal::service('drupal.helper');
            $nid = $service->node->getLatestNodeId();
            $params = $service->helper->get_parameter();
            $arrayData = json_decode($params["data"], TRUE);
            $fields['title'] = "COM-".$nid  ;

            $date_obj = new DrupalDateTime();
            $date = $date_obj->format('Y-m-d');
            $fields['field_date'] =  $date ; 
            $fields['field_total_vente'] = $arrayData['total'] ;

            if(isset($params['client'])){
               $fields['field_client'] = $params['client'] ;
            }
            $fields['field_status'] = "unpayed" ;    
            $items = [];
            foreach($arrayData['items'] as $key => $item){    
            // $article = \Drupal::service('entity_parser.manager')->node_parser( $item["id"]);
            $items[$key] = [
               'field_article' => $item["id"],
               'field_quantite' => $item["qte"]
            ];
            }
            $fields['field_articles'] = $items;
            $com_new = \Drupal::service('crud')->save('node', 'commande', $fields);
            if(is_object($com_new )){
               return $com_new->id();
            }
            return false ;
       
       }
       function savePaymentCommande($id){
         $fields['field_status'] = "unpayed" ;    
         
       }

       function updateStocks($status ,$items){
         
       }
     
      
}
