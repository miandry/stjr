<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 11/6/18
 * Time: 4:11 PM
 */

namespace Drupal\drupal_helper;


use Drupal\user\Entity\User;

class DrupalUser {
  public function hasRole($role_name,$uid=null){
    $userCurrent = \Drupal::currentUser();
    if(is_numeric($uid)){
      $userCurrent = \Drupal::entityTypeManager()
        ->getStorage('user')->load($uid);
    }
    $is=false ;
    if (in_array($role_name, $userCurrent->getRoles())) {
      $is = true ;
    }
    return $is;
  }
  public function get_user_by_email($email){
    return \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(array('mail' => $email));
  }
  public  function is_admin($uid=null) {
    return $this->hasRole('administrator',$uid);
  }
  public function hasPermission($permission,$user = null ){
    if($user==null){
    $user = \Drupal::currentUser();
    }
    if(is_object($user)){
      $user_roles = $user->getRoles();
      $roles_permissions = user_role_permissions($user_roles);
      foreach ($roles_permissions as $role_key => $permissions) {
        if($role_key =='administrator'){
          return true ;
        }
        foreach ($permissions as $permission_item) {
          if($permission_item == $permission){
            return true ;
          }
        }
      }
    }
    return false ;
  }
  public function getUserLastId(){
    $last_user_id = false ;
      $query = \Drupal::entityQuery('user')
      ->sort('uid', 'DESC')
      ->range(0, 1); // Get only one result.
      $result = $query->execute();
      if (!empty($result)) {
      $last_user_id = reset($result);
      }
      return    $last_user_id ;
  }
}