<?php

namespace Drupal\dynamic_layouts\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a deletion confirmation form for DynamicLayout entity.
 */
class DynamicLayoutDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the layout %question_name?', array('%question_name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('dynamic_layout.dynamic_layout_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    $this->logger('dynamic_layout')->notice('Layout %name has been deleted.', array('%name' => $this->entity->label()));
    drupal_set_message($this->t('Layout %name has been deleted.', array('%name' => $this->entity->label())));

    // Clear all plugin caches.
    // This is needed to update the Dynamic Layouts in Display Suite.
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
