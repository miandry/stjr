<?php

namespace Drupal\stripe_custom\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ApiController.
 */
class StripeCustomController extends ControllerBase {

  public function success() {
    return [
        '#markup' => '<p> Here are the contents of your page </p>',
    ];
  }

}
