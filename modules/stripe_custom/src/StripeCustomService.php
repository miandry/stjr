<?php

/**
 * Created by PhpStorm.
 * User: admin
 * Date: 23/03/2018
 * Time: 14:49
 */

namespace Drupal\stripe_custom;



class StripeCustomService 
{

     function successPage(){

     }
     function pageCheckout(){
      

     }
     function test(){
      $config = \Drupal::config('stripe.settings');
      $apikeySecret = $config->get('apikey.' . $config->get('environment') . '.secret');
      \Stripe\Stripe::setApiKey($apikeySecret);
      $subaccount = \Stripe\Account::create([
        'type' => 'express', // or 'express' based on your needs
        'country' => 'US', // replace with the appropriate country code
        'email' => 'boutamiandrilala9@yahoo.fr', // replace with the email address
      ]);     
      echo $subaccount->id;die();

     }
      
}
