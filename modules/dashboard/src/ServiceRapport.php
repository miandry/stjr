<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:49
 */

namespace Drupal\dashboard;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;

class ServiceRapport
{

       function valeurDuStock(){
         // Get the entity query service.
        // Get the entity query service for nodes.
            $entity_query = \Drupal::entityQuery('node');

            // Query for article nodes.
            $nids = $entity_query->condition('type', 'article')
            ->execute();

                // Load the nodes.
            $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
            $sum = 0 ;
            // Output the node titles.
            foreach ($nodes as $article) {
               $stock =  $article->field_quantite_stock->value ;
               $pv = $article->field_prix_unitaire->value  ;
               $sum  = $sum  + floatval($pv)*floatval($stock);

            }
            return ($sum);
       }
       function stockBas($min){
        $entity_query = \Drupal::entityQuery('node');
        $nids = $entity_query->condition('type', 'article')
        ->execute();
        $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
        $stockbas=[] ;
        foreach ($nodes as $article) {
           $stock =  $article->field_quantite_stock->value ;
           if( 0 < $stock &&  $stock <= $min){
            $stockbas[] = $article ;
           }
        }
        return sizeof($stockbas);
       }
       function stockRuputure(){
        $entity_query = \Drupal::entityQuery('node');
        $nids = $entity_query->condition('type', 'article')
        ->execute();
        $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
        $stockbas=[] ;
        foreach ($nodes as $article) {
           $stock =  $article->field_quantite_stock->value ;
           if(  $stock <= 0){
            $stockbas[] = $article ;
           }
        }
        return sizeof($stockbas);
       }
       function topAchactParMois(){

       }
       function topVenteParMois(){
            // Get the current date.
            $current_date = \Drupal::service('datetime.time')->getCurrentTime();

            // Get the first and last day of the current month.
            $first_day_of_month = strtotime('first day of this month', $current_date);

            $entity_query = \Drupal::entityQuery('node');
            // Query for article nodes.
            $nids = $entity_query->condition('type', 'commande')
            ->condition('field_date',$first_day_of_month, '>=')
            ->condition('field_status','payed', '=')
            ->sort('field_date','desc')
            ->range(0,10)
            ->execute();     
                // Load the nodes.

            $top = [] ;
            $sorts=[];
            // Output the node titles.
            foreach ($nids as $nid) {
                $commande = \Drupal::service('entity_parser.manager')->node_parser($nid);
                $paras =   $commande["field_articles"];
                foreach ( $paras as $p) {
                    $para = \Drupal::service('entity_parser.manager')->paragraph_parser($p["id"]);
                    $id = $para['field_article']["nid"];
                    $top[$id]['name'] = $para['field_article']['title'];
                    $top[$id]['num'] =  $top[$id]['num'] + floatval($para["field_quantite"]);   
                    $sorts[$id] = $top[$id]['num'] ;    
                }
            }
            uasort($sorts, function($a, $b) {
                return $b - $a;
            });
            $sort_final = [];
            foreach ($sorts as $key =>  $s) {
                $sort_final[] =  $top[$key];
            }

            return  $sort_final;   
       }
       function totalVenteParMois(){
            // Get the current date.
            $current_date = \Drupal::service('datetime.time')->getCurrentTime();

            // Get the first and last day of the current month.
            $first_day_of_month = strtotime('first day of this month', $current_date);

            $entity_query = \Drupal::entityQuery('node');
            // Query for article nodes.
            $nids = $entity_query->condition('type', 'commande')
            ->condition('field_date',$first_day_of_month, '>=')
            ->condition('field_status','payed', '=')
            ->execute();     
                // Load the nodes.
            $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);
            $sum = 0 ;
            // Output the node titles.
            foreach ($nodes as $commande) {
                $pv = $commande->field_total_vente->value  ;
                $sum  = $sum  + floatval($pv) ;
                            
            }
            return ($sum);
       }
       function dateProche(){
        
       }
    

}
