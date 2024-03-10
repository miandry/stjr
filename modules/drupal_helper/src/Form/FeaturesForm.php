<?php

namespace Drupal\drupal_helper\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Component\Serialization\Yaml;
/**
 * Class ContentExportSettingForm.
 */
class FeaturesForm extends FormBase {

//  /**
//   * {@inheritdoc}
//   */
  protected function getEditableConfigNames() {
    return [
      'drupal_helper.features',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'features';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('drupal_helper.features');
    $val = $config->get('redirect_to_login_if_not_connect');
      $form['redirect_to_login_if_not_connect'] =[
        '#type' => 'checkbox',
        '#title' => $this->t('Redirect to user/login if not connect'),
        '#default_value' => $val,
      ];

      $form['actions'] = ['#type' => 'actions'];
      $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),

      ];
    return $form ;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory()->getEditable('drupal_helper.features')
    ->set('redirect_to_login_if_not_connect', $form_state->getValue('redirect_to_login_if_not_connect'))
    ->save();
  }

}
