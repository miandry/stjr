<?php

namespace Drupal\stjr\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
/**
 * Class UploadForm.
 */
class UploadForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
  
    $form['lignes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Lignes a collecte '),
      '#maxlength' => 30,
      '#description' => 'Exemple: 19',
      '#default_value' => '1',
      '#required' => TRUE,
    ];


    $form['excel_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Excel File'),
      '#upload_validators' => [
        'file_validate_extensions' => ['xlsx xls'], // Validate extensions.
      ],
      '#upload_location' => 'public://', // Define a directory in the public file system.
      '#required' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Upload'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
   // $range = $form_state->getValue('lignes');
    // This regex matches any string that consists of a number, optional spaces, a dash, optional spaces, and another number.
    // if (!preg_match('/^\d+\s*-\s*\d+$/', $range)) {
    //   $form_state->setErrorByName('lignes', $this->t('The Lignes must be in the format "number - number".'));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_id = reset($form_state->getValue('excel_file'));
    $lig = ($form_state->getValue('lignes'));

    if ($file_id) {
      
      $file = File::load($file_id);
      if ($file) {
        $file->setPermanent();
        $file->save();
        $this->importPreprocessExcel($file->id(), $lig);
        \Drupal::messenger()->addMessage($this->t('File uploaded and processed successfully.'));
      }

    }
  }

  function importPreprocessExcel($id,$lig){


    $result_final = [];
    $file = File::load($id);
    $uri = $file->getFileUri();
    $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($uri);
    $file_path = $stream_wrapper_manager->realpath();
    if (file_exists($file_path)) {
  

        $phpexcel = \Drupal::service('phpexcel');
        $custom_calls = [
          'getActiveSheet' => [TRUE],
        ];
        $result = $phpexcel->import($file_path, true, false, $custom_calls);

        //  kint($result);die();
        //  $service = \Drupal::service('mz_transfer.default');

        foreach ($result[0] as $key => $item) {
           if(floatval($lig) < $key){
               if($item[1] !=""&&$item[2] !=""  && $item[0] !=""  ){
                 $result_final[] = $item;
               }
            }
        }
        $service = \Drupal::service('drupal.helper');
        $service->helper->storage_set('excel',$result_final);
        $service->helper->redirectTo('/planning');   
        exit();   
  

    }
    return true ;
   }



}
