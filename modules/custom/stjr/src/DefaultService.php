<?php

namespace Drupal\stjr;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\file\Entity\File;
use Drupal\Core\Messenger\MessengerInterface;
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
          if($personne){
            $entity->field_personnes[] =  ['target_id' => $personne->id()];
          }else{

          }
        }      
        return $entity ;
   }

   function formatItemPersonne($person , $collection){
    if($collection["field_client"]["title"] !=  $person["field_employeur"]["title"]){
      \Drupal::messenger()->addMessage(t('Personne travaille en @person , Mais sur la collection du client @coll ', ['@coll' => $collection["field_client"]["title"], '@person' => $person["field_employeur"]["title"]]), 'warning');
    }
    return [
      "matricule" => $person["title"],
      "nom" => $person["field_nom"],
      "prenom" => isset($person["field_prenom"])?$person["field_prenom"]:"",
      "client" => isset($collection["field_client"])?$collection["field_client"]["title"]:"",
      "heure" => isset($collection["field_heure_de_depot_"])?$collection["field_heure_de_depot_"]["title"]:"",
      "date" => isset($collection["field_date"])?$collection["field_date"]:"",
      "type" => isset($collection["field_type"])?$collection["field_type"]:"",
      "adresse" => isset($person["field_adresse"][0]["title"])?$person["field_adresse"][0]["title"]:"",
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
 

        $query = \Drupal::entityQuery('node')
        ->condition('type', 'personnel')
        ->condition('title',  $matricule); // Only published nodes.
        $existing_nids = $query->execute();
        if (!empty($existing_nids)) {}
        $query =  \Drupal::database()->select('node__field_personnes','fqs');
        $query->join('node__field_date', 'u', 'fqs.entity_id = u.entity_id');
        $query->join('node__field_heure_de_depot_', 'heure', 'heure.entity_id = u.entity_id');
        $query->join('node__field_nom', 'nom', 'fqs.field_personnes_target_id = nom.entity_id');
   
       $query->condition('u.field_date_value',$selected_date,'='); 
       if(isset($params["heure"])){
          $query->condition('heure.field_heure_de_depot__target_id',$params["heure"],'='); 
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






}
