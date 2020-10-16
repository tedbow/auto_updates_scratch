<?php


namespace Drupal\upper\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class UpdaterForm extends FormBase {

  public function getFormId() {
    return 'upper_update_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['update_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The version to update to'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Update',
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $version = $form_state->getValue('update_version');
    $this->messenger()->addMessage("would have updated to: " . $form_state->getValue('update_version'));
    \Drupal::service('upper.updater')->update($version);
  }

}