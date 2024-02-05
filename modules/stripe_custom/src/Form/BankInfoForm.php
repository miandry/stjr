<?php

namespace Drupal\stripe_custom\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BankInfoForm.
 */
class BankInfoForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stripe_custom_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
      $query = $this->getRequest()->query;
      $form['key'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Name Bank'),
          '#attributes' => ['name' => 'key'],
          '#default_value' => ($query->get('key'))?$query->get('key'):''
      ];
      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => 'Save',

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
   
    
  }

}
