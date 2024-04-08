<?php

/**
 * Created by PhpStorm.
 * User: USER
 * Date: 6/7/18
 * Time: 5:47 PM
 */
namespace Drupal\drupal_helper;


class DrupalNodeHelper extends DrupalCommonHelper
{

    public function __construct()
    {

    }
    public function getIdNodeByTitle($type,$title)
    {
        $query = \Drupal::entityTypeManager()->getStorage("node")->getQuery();
        $query->condition("type", $type);
        $query->condition("title", $title);
        $t = $query->execute();
        if(empty($t)){
            return null ;
        }else{
            return end($t);
        }
    }
    public function getIdNodeByField($type,$field_name,$field_value)
    {
        $query = \Drupal::entityTypeManager()->getStorage("node")->getQuery();
        $query->condition("type", $type);
        $query->condition($field_name, $field_value);
        $t = $query->execute();
        if(empty($t)){
            return null ;
        }else{
            return end($t);
        }
    }
    public function getNodeByAlias($alias)
    {
        /** @var \Drupal\Core\Path\AliasManager $alias_manager */
        $alias_manager = \Drupal::service('path_alias.manager');
        $parts = explode('+', $alias);
        $alias = implode('/', $parts);

        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        try {
            $path = $alias_manager->getPathByAlias($alias);
            $route = Url::fromUserInput($path);
            if ($route && $route->isRouted()) {
                $params = $route->getRouteParameters();
                if (!empty($params['node'])) {
                    return $node_storage->load($params['node']);
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }
    /**
     * Get the latest node ID.
     */
    function getLatestNodeId() {
        $entity_type_manager = \Drupal::service('entity_type.manager');
        $node_storage = $entity_type_manager->getStorage('node');
        $query = $node_storage->getQuery()
        ->sort('nid', 'DESC')
        ->range(0, 1);
        $nids = $query->execute();
        if (!empty($nids)) {
        return reset($nids);
        }
        return NULL;
    }
}