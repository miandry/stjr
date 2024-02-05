<?php

namespace Drupal\drupal_helper\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\drupal_helper\DrupalHelper;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiController.
 */
class ApiController extends ControllerBase {

  /**
   * Paragraph_delete.
   *
   * @return string
   *   Return Hello string.
   */
  public function paragraph_delete($id) {
      $drupal_helper = new DrupalHelper();
      $destination = "/".$drupal_helper->helper->get_parameter('destination');
      $paragraph_list =  \Drupal::entityTypeManager()->getStorage('paragraph')->loadByProperties(array('id' => $id));
      if(!empty($paragraph_list)){
          $para_object = array_values($paragraph_list)[0] ;
          $para_object->delete();
      }
      if($destination){
          return $drupal_helper->helper->redirectTo($destination) ;
      }else{
          return $drupal_helper->helper->redirectTo('/') ;
      }
  }
  public function delete($entity_type,$id) {
        $entity =  \Drupal::entityTypeManager()->getStorage($entity_type)->load($id);
        $json['status']= 0;
        if(is_object($entity)) {
            $entity->delete();
            $json['status']=1;
        }
      return new JsonResponse($json);
  }

}
