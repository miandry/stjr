<?php

namespace Drupal\templating;

use Drupal\Component\Diff\Diff;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityInlineTemplate extends BaseServiceEntityInlineTemplate
{

  function getTemplateView($variables)
  {
    $view_name = $variables['id'];
    $view_display = $variables['display_id'];
    $theme = $this->is_allowed();
    if (!$theme) {
      return false;
    }
    $config_name = "view--" . $theme . '-' . trim($view_name) . '-' . trim($view_display);
    $suggestion_1 = $this->formatName($config_name);
    $templates_views = \Drupal::entityQuery('node')
      ->condition('type', 'templating')
      ->condition('status', '1')
      ->condition('title', $suggestion_1, 'STARTS_WITH')
      ->execute();
    $results = [];
    if (!empty($templates_views)) {
      foreach ($templates_views as $id) {
        $template = \Drupal::entityTypeManager()->getStorage('node')->load($id);
        $view_section = $template->field_templating_mode_view->value;
        $template_content = $template->field_templating_html->value;
        $results[$view_section] = $template_content;
      }

    }
    return $results;
  }


  function getTemplateEntity($entity, $view_mode)
  {
    $output = $this->getTemplatingDatabase($entity, $view_mode);
    return $output;
  }

  function getEntityFromVariable($var, $entity = null)
  {
    if ($entity == "block"  && isset($var["content"]['#block_content'])) {
      $content = $var["content"];
      $entity_result =  (is_object($content['#block_content']))? $content['#block_content'] : $content['content']['#block_content'];
    } else {

      $entity_result =    isset($var['elements']["#" . $entity])?$var['elements']["#" . $entity]:null;
    }

    return $entity_result;

  }
  function getTemplatingDatabaseCustom($hook_name)
  {
    $theme = $this->is_allowed();
    if (!$theme) {
      return false;
    }
    $output = false;
    $content = $this->getTemplatingByTitle($hook_name);
    if (is_object($content)) {
      $output = $content->field_templating_html->value;
    }

    return $output;
  }

  function getTemplatingDatabase($entity, $mode_view = null)
  {
    $theme = $this->is_allowed();
    if (!$theme) {
      return false;
    }
    if($mode_view == null){
      $mode_view = "full";
    }
    $entity_name = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $id = $entity->id();
    $output = false;
    $hook_name = $this->formatName($entity_name . '--' . $theme . '-' . $bundle . "-" . $mode_view . ".html.twig");
    $content = $this->getTemplatingByTitle($hook_name);
    if (!is_object($content)) {
      $theme_base = $this->baseTheme($theme);
      $hook_name_base =  $this->formatName($entity_name . '--' . $theme_base . '-' . $bundle . "-" . $mode_view . ".html.twig");
      $content_base = $this->getTemplatingByTitle($hook_name_base);
      if (is_object($content_base)) {
        $content = $content_base;
      }
    }
    $hook_name_id = $this->formatName($entity_name . '--' . $theme . '-' . $bundle . "-" . $id . "-" . $mode_view . ".html.twig");
    $content_id = $this->getTemplatingByTitle($hook_name_id);
    if (is_object($content_id)) {
      $content = $content_id;
    }
    if (is_object($content)) {
      $output = $content->field_templating_html->value;
    }

    return $output;
  }

  function getTemplatingPreview($entity, $template)
  {
    $theme = $this->is_allowed();
    if (!$theme) {
      return false;
    }
    $output = $template->field_templating_html->value;
    return $output;
  }

  public function getLastEntityContent($bundle, $type = 'block_content')
  {
    $block_id = 0;
    $query = \Drupal::entityQuery($type);
    $query->condition('type', $bundle);
    $query->range(0, 1);
    $res = $query->execute();
    if (!empty($res)) {
      $block_id = end($res);
      return \Drupal::entityTypeManager()->getStorage($type)->load($block_id);
    }
    return false;

  }

  function generateLibrary()
  {
    $libs = $this->getLibrary();
    $output = [];
    if ($libs) {
      $url = Url::fromRoute('<current>');
      $str_url = \Drupal::service('path_alias.manager')->getAliasByPath($url->toString());
      foreach ($libs as $node) {

        $current_theme = \Drupal::theme()->getActiveTheme();
        $theme = $current_theme->getName();

        $mytheme = $node->field_theme->value;
        $type = $node->field_lib_type->value;
        $position = $node->field_lib_position->value;
        $paths = $node->field_lib_condition->value;
        $array_paths = explode(PHP_EOL, $paths);
        $array_paths = array_map(
          function ($item) {
            return is_string($item) ? trim($item) : $item;
          },
          $array_paths
        );
        $allowed_path = false;
        if (in_array("*", $array_paths)) {
          $allowed_path = true;
        }
        if (in_array($str_url, $array_paths)) {
          $allowed_path = true;
        }
        if ($allowed_path && $mytheme == $theme) {
          $is_external = $this->is_field_ready($node, 'field_lib_url');
          $url = false;
          // if external or internal
          if ($is_external) {
            $url = trim($node->field_lib_url->value);
          } else {
            $is_file = $this->is_field_ready($node, 'field_lib_file');
            if ($is_file) {
              $file = File::load($node->field_lib_file->target_id);
              if (is_object($file)) {
                $url = URl::fromUri(file_create_url($file->getFileUri()))->toString();
              }
            }
          }
          // if css or js
          if ($url) {
            if(!isset($output[$type . '_' . $position])){ $output[$type . '_' . $position] = "" ;}
            if ($type == "css") {
              $output[$type . '_' . $position] = $output[$type . '_' . $position] . '<link rel="stylesheet" href="' . $url . '" crossorigin="" />';
            }
            if ($type == "js") {
              $output[$type . '_' . $position] = $output[$type . '_' . $position] . '<script src="' . $url . '" crossorigin=""></script>';
            }
          }

        }
      }

    }
    return $output;
  }

  function getLibrary()
  {
    $results = false;
    $libs = \Drupal::entityQuery('node')
      ->condition('type', 'library')
      ->condition('status', '1')
      ->execute();
    $results = [];
    if (!empty($libs)) {
      foreach ($libs as $id) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
        if ($node->status && $node->status->value == 1) {
          $results[$id] = $node;
        }
      }

    }
    return $results;
  }

  function htmlPage()
  {
    $node = \Drupal::request()->attributes->get('node');
    $is_ready = $this->is_field_ready($node, 'body');
    if ($node && $node->bundle() == 'html_page' && $is_ready) {
      $body = $node->body->value;
      $var['entity'] = $node;
      return [
        '#type' => 'inline_template',
        '#template' => $body,
        '#context' => $var
      ];
    }
    return null;
  }

  public static function importFinishedCallback($success, $results, $operations)
  {
    if ($success) {
      $message = t('Template export successfully');
      \Drupal::messenger()->addMessage($message);
    }

    return new RedirectResponse(Url::fromRoute('view.templating.page_1')->toString());
  }

  public function exportTemplating($template)
  {
    $file_path = $this->getFilepathTemplating($template);
    $myfile = fopen($file_path, "wr") or \Drupal::logger('templating')->error($file_path . "can not write");
    $txt = $template->field_templating_html->value;
    fwrite($myfile, $txt);
    fclose($myfile);
    $message = t('Template export successfully');
    \Drupal::messenger()->addMessage($message);
    return true;
  }

  public function diff($template)
  {
    $service = \Drupal::service('templating.manager');
    $file = $service->getFilepathTemplating($template);
    $content_html = file_get_contents($file);
    $txt = $template->field_templating_html->value;
    $diffFormatter = \Drupal::service('diff.formatter');

    $from = explode("\n", $content_html);
    $to = explode("\n", $txt);
    $diff = new Diff($from, $to);
    $diffFormatter->show_header = FALSE;
    return $diffFormatter->format($diff);
  }

  public function getFilepathTemplating($template)
  {
    $file_name = ($template->label());
    $themeHandler = \Drupal::service('theme_handler');
    $themePath = $themeHandler->getTheme($template->field_templating_theme->value)->getPath();
    return (DRUPAL_ROOT . '/' . $themePath . '/templates/templating/' . $file_name);
  }

  public function getRenderTemplateCustom($content)
  {
    $output = false;
    $current_theme = \Drupal::theme()->getActiveTheme();
    $theme = $current_theme->getName();
    if(isset($content['element']) && isset($content['element']['form_id']) && isset($content['element']['form_id']['#id'])){
      $hook_name_base = $this->formatName("custom--".$theme."-".$content['element']['form_id']['#id'].".html.twig");
      $content_base = $this->getTemplatingByTitle($hook_name_base);
      if (is_object($content_base)) {
        $output = $content_base->field_templating_html->value;
      }
    }
    // comment.html.twig
    if (isset($content['comment_body'])) {
      $hook_name_base = $this->formatName("comment--" . $theme . ".html.twig");
      $content_base = $this->getTemplatingByTitle($hook_name_base);
      if (is_object($content_base)) {
        $output = $content_base->field_templating_html->value;
      }
    }
    if ($output) {
      return [
        '#type' => 'inline_template',
        '#template' => $output,
        '#context' => [
          'var_template' => $content
        ],
      ];
    }
    return false;
  }
  function addLibrary(){
    $library_name = 'templating/confirmjs';
    $library_definition = [
      'version' => '1.x',
      'js' => [
        'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js' => [],
      ],
      'dependencies' => [
        'core/jquery',
      ],
    ];
  
    // Add or modify the library definition.
    \Drupal::service('library.discovery')->setLibraryInfo($library_name, $library_definition);
  
  }
}
