<?php

namespace Drupal\custom_vbo_form_action\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\RedirectResponse;



/**
 * Fournit une action avec un formulaire VBO.
 *
 * @Action(
 *   id = "custom_node_form_action",
 *   label = @Translation("Executer la repartition"),
 *   type = "node"
 * )
 */
class CustomNodeFormAction extends ActionBase {


  public function execute($entity = NULL) {
    // Required by interface, even if unused.
  }
    /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    /** @var \Drupal\Core\Entity\EntityInterface[] $entities */
    $temp_store_factory = \Drupal::service('session_based_temp_store');
    $uid = \Drupal::currentUser()->id();// User ID
    $temp_store = $temp_store_factory->get($uid.'_custom_vbo_collect', 106400); 
    $temp_store->deleteAll();

    $service = \Drupal::service('stjr.default');
    $autoResults = $service->autoRepartitions($entities);
    $temp_store->set('transports', $autoResults);
    $response = new RedirectResponse('/gestion', 302);
    $response->send();
    return;
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = $object->access('update', $account, TRUE);
    return $return_as_object ? $result : $result->isAllowed();
  }
}
