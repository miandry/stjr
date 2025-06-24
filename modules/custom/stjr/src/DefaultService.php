<?php

namespace Drupal\stjr;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\file\Entity\File;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Url;
use \Drupal\node\Entity\Node;

/**
 * Class DefaultService.
 */
class DefaultService {

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct() {

  }

  public function savePersonnel(){
      $paramater =  \Drupal::request()->query->all();
      if(isset($paramater['action']) &&$paramater['action'] == 'save_person' ){


      // $current_url = "/admin";
      // $response = new RedirectResponse($current_url);
      // $response->send();
      // return;
      }
   
    
  }
  function verifyExcel($excel_preview){

  }
  function verifyPersonnelExist($matricule){

  }
    function isPersonnelExist($matricule){

          $query = \Drupal::entityQuery('node')
          ->condition('type', 'personnel')
          ->condition('title',  $matricule); // Only published nodes.
          $existing_nids = $query->execute();
          if (!empty($existing_nids)) {
            $id = end($existing_nids);
            $entity_type_manager = \Drupal::entityTypeManager();
            return $entity_type_manager->getStorage("node")->load($id);
          }
          return false ;
    }

    
   function insertPersonnel($item){
    $fields['title'] = 0 ;
    $fields['field_prenom'] = 0 ;
    $fields['field_contact'] = 0 ;
    $fields['field_adresse'] = 0 ;
    $fields['field_departement'] = 0 ;
    $booking_new = \Drupal::service('crud')->save('node', 'personnel', $fields);
   }

   function buildCollection($entity){
        $matricule_str = $entity->field_content->value ;
        $matricule_array = explode("\n", trim($matricule_str));  
        $entity->field_personnes = [];
        foreach(  $matricule_array  as $mat){
          $personne = $this->isPersonnelExist(intval($mat));
          $client = $entity->field_client->entity->label();
          if($personne && $personne->field_employeur->target_id == $entity->field_client->target_id){
            $entity->field_personnes[] =  ['target_id' => $personne->id()];
          }else{
            \Drupal::messenger()->addMessage(t('Personne  @person ne travaille pas chez @coll , Mais sur la collection du client @coll ', ['@coll' => $client , '@person' => $personne->label()]), 'error');
            // $current_url = \Drupal::request()->getRequestUri();
            // $response = new RedirectResponse(Url::fromUserInput($current_url)->toString());
            // $response->send();
          }       
        }      
        return $entity ;
   }
   function formatItemPersonne($person , $collection){
    if(isset( $person["field_employeur"]["title"]) && $collection["field_client"]["title"] !=  $person["field_employeur"]["title"]){
      \Drupal::messenger()->addMessage(t('Personne travaille en @person , Mais sur la collection du client @coll ', ['@coll' => $collection["field_client"]["title"], '@person' => $person["field_employeur"]["title"]]), 'warning');
    }
    return [
      "collection_id" => ($collection["nid"]),
      "personne_id" => ($person["nid"]),
      "matricule" => $person["title"],
      "name" => $person["field_nom"],
      "prenom" => isset($person["field_prenom"])?$person["field_prenom"]:"",
      "client" => isset($collection["field_client"])?$collection["field_client"]["title"]:"",
      "heure" => isset($collection["field_heure_de_depot_"])?$collection["field_heure_de_depot_"]["title"]:"",
      "date" => isset($collection["field_date"])?$collection["field_date"]:"",
      "type" => isset($collection["field_type"])?$collection["field_type"]:"",
      "address" => isset($person["field_adresse"][0]["title"])?$person["field_adresse"][0]["title"]:"",
      "address_id" => $person["field_adresse"][0]["tid"],
      "contact" => isset($person["field_contact"])?$person["field_contact"]:""
    ];     
   }
   function getCollectionByFilter(){
        $service_helper = \Drupal::service('drupal.helper');
        $params = $service_helper->helper->get_parameter();

        $current_now = \Drupal::service('datetime.time')->getCurrentTime();
        $selected_date = date('Y-m-d', $current_now);

        if($params["date"]){
          $selected_date = $params["date"] ; 
        }
        if(empty($params)){
          $params["date"] = $selected_date ;
        }

        // $query = \Drupal::entityQuery('node')
        // ->condition('type', 'personnel')
        // ->condition('title',  $matricule); // Only published nodes.
        // $existing_nids = $query->execute();
        // if (!empty($existing_nids)) {

        // }
        $query =  \Drupal::database()->select('node__field_personnes','fqs');
        $query->join('node__field_date', 'u', 'fqs.entity_id = u.entity_id');
        $query->join('node__field_heure_de_depot_', 'heure', 'heure.entity_id = u.entity_id');
        $query->join('node__field_nom', 'nom', 'fqs.field_personnes_target_id = nom.entity_id');
   
      // $query->condition('u.field_date_value',$selected_date,'='); 
       if(isset($params["heure"])){
        //  $query->condition('heure.field_heure_de_depot__target_id',$params["heure"],'='); 
       }
       if(isset($params["search"])){
        $query->condition('nom.field_nom_value', '%' .$params["search"].'%' ,'LIKE'); 
       }

       $query->addField('fqs', 'field_personnes_target_id', 'id'); 
       $query->addField('fqs', 'entity_id', 'collection_id'); 
        // Execute the query.
        $result = $query->execute();
        // Fetch the results as an associative array.
        $rows =  ($result->fetchAllAssoc('id'));
        $items = [];
        if(!empty($rows)){
          $service = \Drupal::service('entity_parser.manager');
          foreach($rows as $r){
            $personnes = $service->node_parser($r->id);
            $collection = $service->node_parser($r->collection_id);
            $items[] = $this->formatItemPersonne( $personnes , $collection );

          }
        }

        $heures = $service_helper->helper->taxonomy_load_multi_by_vid("heure");

        return ["items" =>$items,"filter"=>$params , "heures"=>  $heures] ;

      
   }

   function getItinaires($client = null ){
        $query = \Drupal::entityQuery('node')
        ->condition('type', 'itinaire');
        if($client){
          $query->condition('field_client', $client);
        }
        $query->condition('status', 1); 
        $rows = $query->execute();
        $items = [];
        if(!empty($rows)){
          $service = \Drupal::service('entity_parser.manager');
          foreach($rows as $nid){
            $node = $service->node_parser($nid);
            $title = $node['title'] ." - ".$node['field_adresse'][0]['title']."  →  ". end($node['field_adresse'])['title'] ;
            $nbrplace = 14 ;
            if(isset($node['node']['field_voiture'])){
              $voiture = ($node['node']['field_voiture']);
              $nbrplace = $voiture->field_nombre_places->value ;
            }
            $items[] = [
              'id' => $nid ,
              'title' => $title,
              'node' =>  $node,
              'place_number' => $nbrplace
            ];
          }
        }
        return $items;
   }
   function autoRepartitions($collections){

      //  if(isset($params["auto"])&& $params["auto"] == 1){

          $client =  $collections[0]->field_client->target_id ;
          $itinaires = $this->getItinaires($client);
          $results = [] ;
          foreach($collections as $collection){
            $service = \Drupal::service('entity_parser.manager');
            $item = $service->node_parser($collection);
            $type = $item["field_type"];

            if(isset($item['field_personnes'])){
              foreach($item['field_personnes'] as $personne){
                $personne  = $service->node_parser($personne['node']);
                $results = $this->checkPersonne($type,$item["nid"], $personne ,$itinaires, $results);
              }   
            }   
          }
        
         foreach( $results["selected"] as $key => $it){
              if(isset($results["selected"][$key][0])){
                $voiture = ($results["selected"][$key][0]['ligne']['node']['field_voiture']['node']);
                $nbrplace = $voiture->field_nombre_places->value ;
              }
              if(sizeof($it) <  floatval($nbrplace) + 1 ){
                for ($i = sizeof($it); $i < floatval($nbrplace) + 1 ; $i++) {
                   $results["selected"][$key][$i] = [] ;
                }
              }
         }
        return ($results); 
       // }
   }
   function checkPersonne($type,$collect_id,$personne,$itinaires,$results){
    $not_selected = true ;   
    foreach($itinaires as $it){
      $it_adresses = ($it["node"]["field_adresse"]);
      if($type=="RAMASSAGE"){
        $it_adresses = array_reverse($it["node"]["field_adresse"]);
      }

      foreach($it_adresses as $it_adress){
            $address_id = $personne["field_adresse"][0]['tid'];
            if($it_adress['tid'] ==  $address_id){
              $results["selected"][$it["id"]][] = [
                "person" => $personne ,
                "ligne" => $it ,
                "collect_id" => $collect_id
              ];
              $not_selected = false ;
            }

            
      }
    }
    if($not_selected){
      $personne["collect_id"] = $collect_id;
      $results["not_selected"][] = $personne ;
    }

    return  $results ;
  }
  // creer les transports in database
  function buildTransports(){
    $paramaters =  \Drupal::request()->query->all();
    $fields = [] ;
    $resultats = [];
    $service = \Drupal::service('entity_parser.manager');
    foreach($paramaters['passagers'] as $p){
      $parray = explode("####", $p); 
      $collect = $service->node_parser($parray[0]);
      $personne = $service->node_parser($parray[1]);
      $ligne  = $service->node_parser($parray[2]);
      $place = $parray[3] ;
      $fields[$parray[2]][] = [
        "person" => $personne ,
        "ligne" =>   $ligne ,
        "place" =>  $place,
        "collect" =>  $collect
      ];
    }  
    $entity_type_manager = \Drupal::entityTypeManager();
    foreach($fields as $key => $p){
      $trans = []; 
      $trans['title'] = 'transfer_'.$key.'_'.date("Y-m-d H:i:s") ;
      $trans['field_itinaire'] =  intval($key);
      $trans['field_heure_de_depot_'] = intval($p[0]["collect"]["field_heure_de_depot_"]["tid"]);
      $trans["field_type"] = $p[0]["collect"]["field_type"];
      $trans["field_client"] = intval($p[0]["ligne"]["field_client"]["nid"]);
      foreach($p as $item){
        $person_id  = $item["person"]["nid"];
        $trans["field_personnels"][] = 
           [
            "field_personnel" =>  $person_id ,
            "field_place" => $item["place"]
           ];
      }
      $trans_new = \Drupal::service('crud')->save('node', 'transport', $trans);
      $trans_array = $service->node_parser($trans_new) ;
      $timestamp = strtotime($p[0]["collect"]["field_date"]);
      $formatter = new \IntlDateFormatter(
        'fr_FR',
        \IntlDateFormatter::LONG,
        \IntlDateFormatter::NONE,
        'Indian/Antananarivo',
        \IntlDateFormatter::GREGORIAN,
        'd MMMM yyyy'
      );
      $resultats[] = [
           "date" => $formatter->format($timestamp) ,
           "voiture" => [
               "title" => $p[0]["ligne"]["field_voiture"]["title"], 
               "details" => isset($p[0]['ligne']['field_voiture']['node']->field_details->value)? $p[0]["ligne"]["field_voiture"]["node"]->field_details->value : ""
              ],
           "chauffeur" => [
              "nom" => $p[0]["ligne"]["field_chauffeur"]["title"],
              "contact" => isset($p[0]["ligne"]["field_chauffeur"]["node"]->field_contact->value)?$p[0]["ligne"]["field_chauffeur"]["node"]->field_contact->value : ""
            ],
           "node" =>   $trans_array 
      ];


    }
    $temp_store_factory = \Drupal::service('session_based_temp_store');
    $uid = \Drupal::currentUser()->id();// User ID
    $temp_store = $temp_store_factory->get($uid.'_custom_vbo_collect', 106400); 
    $temp_store->set('transports_ready', $resultats);
    return  $resultats ;
  }
  function resetTransport(){
    $temp_store_factory = \Drupal::service('session_based_temp_store');
    $uid = \Drupal::currentUser()->id();// User ID
    $temp_store = $temp_store_factory->get($uid.'_custom_vbo_collect', 106400); 
    $trans_current =  $temp_store->get('transports_ready');
    
    foreach($trans_current as $i){
      $nid  = $i["node"]["nid"];
      $node = Node::load($nid);
      if ($node) {
        $node->delete();
      }
    }
    $temp_store->delete('transports_ready');
  }
  function printTransports(){
    $temp_store_factory = \Drupal::service('session_based_temp_store');
    $uid = \Drupal::currentUser()->id();// User ID
    $temp_store = $temp_store_factory->get($uid.'_custom_vbo_collect', 106400); 
    $trans_current =  $temp_store->get('transports_ready');
    $prepares=[];
    foreach($trans_current as $key => $row){
      $prepares[$key]['details'] =  $row ;
      foreach($row["node"]["field_personnels"] as $person){
        $place = $person["field_place"];
        $nom = $person["field_personnel"]["node"]->field_nom->value ;
        $adress = $person["field_personnel"]["node"]->field_adresse->entity->label() ;
        $contact = $person["field_personnel"]["node"]->field_contact->value ;
        $prepares[$key]['personnels'][] = ["place" =>  $place ,"matricule"=>$person["field_personnel"]["title"],"nom" => $nom ,"adresse" => $adress ,"contact" => $contact];
      }
    }
    return  $prepares ;
  }
  function exportTransports(){
    $temp_store_factory = \Drupal::service('session_based_temp_store');
    $uid = \Drupal::currentUser()->id();// User ID
    $temp_store = $temp_store_factory->get($uid.'_custom_vbo_collect', 106400); 
    $trans_current =  $temp_store->get('transports_ready');
    $prepares=[];
    $prepares[] = [];
    foreach($trans_current as $row){
      $prepares[] = [null, $row["node"]["field_client"]["title"] , $row["node"]["field_itinaire"]["title"]];
      $prepares[] = [null ,$row["date"]  , " ", $row["node"]["field_type"] , $row["node"]["field_heure_de_depot_"]["title"]];
      $prepares[] = [null , "Voiture" , $row["voiture"]["title"] ];
      $prepares[] = [null , "Chauffeur" , $row["chauffeur"]["nom"],"Contact",$row["chauffeur"]["contact"]];
      $prepares[] = [];
      $prepares[] = [];

      $prepares[] = [null,null,null,null,null,"PRENOM","ADRESSE","CONTACT"];
      foreach($row["node"]["field_personnels"] as $person){
        $nom = $person["field_personnel"]["node"]->field_nom->value ;
        $adress = $person["field_personnel"]["node"]->field_adresse->entity->label() ;
        $contact = $person["field_personnel"]["node"]->field_contact->value ;
        $prepares[] = [null,null,null,null,null,$nom , $adress ,$contact  ];
      }
      $prepares[] = [];
    }

    $filename = "export_".strtolower($trans_current[0]["node"]["field_client"]["title"])."_". $trans_current[0]["node"]["field_type"] ."_".$trans_current[0]["node"]["field_heure_de_depot_"]["title"].".csv";
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    ob_end_clean();
    $header = ["Date", ["date"] ];
    $handle = fopen('php://output', 'w');
    if( $fields){
      fprintf($handle,chr(0xEF).chr(0xBB).chr(0xBF));
      fputcsv($handle, $fields);
    }
    foreach($prepares as $row){
        fprintf($handle,chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($handle, $row);
    }
    fclose($handle);
    ob_flush();
    exit();

   // $service_helper = \Drupal::service('drupal.helper');
   // $service_helper->helper->exportToCSV($trans_current,[]);
  }
}

