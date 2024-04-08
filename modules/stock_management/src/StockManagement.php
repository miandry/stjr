<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:49
 */

namespace Drupal\stock_management;



class StockManagement 
{

     function calculatePrixDeVente($achat , $marge ){
        return   $achat + ( $achat * $marge)/100  ;
     }
     function updateStockNumberOnDeleteStock($entity){
        $article = $entity->field_article->entity;
        $nbrStock = 0;
        if($article->field_quantite_stock 
            && $article->field_quantite_stock->value ){
            $nbrStock = $article->field_quantite_stock->value ;
        }
        $article->field_quantite_stock->value = $nbrStock - $entity->field_quantite->value;
        return $article->save();
     }
     function executeStockInsert($entity){
        $article = $entity->field_article->entity;
      
        // stock update
        $nbrStock = 0;
        if($article->field_quantite_stock 
            && $article->field_quantite_stock->value ){
            $nbrStock = $article->field_quantite_stock->value ;
        }
        $article->field_quantite_stock->value = $nbrStock + $entity->field_quantite->value;

        // prix vente
        $achat = $entity->field_prix_d_achat->value;
        $marge = $article->field_marge->value ;
        $pv  =   $this->calculatePrixDeVente($achat , $marge )  ;

        // update article
        $article->field_prix_d_achat->value =  $achat ;
        $article->field_prix_unitaire->value = $pv ;
        $entity->field_prix_unitaire->value = $pv ;
        $article->save();
        return  $entity ;
     }
     function executeComputedBenefice($entity){
           $achat = $entity->field_prix_d_achat->value;
           $article = $entity->field_article->entity;
           $marge = $article->field_marge->value ;
           $pv  =   $this->calculatePrixDeVente($achat , $marge)  ;
           return $pv - $achat ; 
     }
      
}
