<?php

namespace Drupal\dynamic_layouts\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dynamic_layouts\DynamicLayoutConstants as Constants;

/**
 * EditRowModalForm class.
 */
class EditRowModalForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * EditRowModalForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_row_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $entity_id = \Drupal::request()->get(Constants::ENTITY_ID);
    $row_id = \Drupal::request()->get(Constants::ROW_ID);

    $row_classes = '';
    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    if ($config_entity = $this->entityTypeManager->getStorage('dynamic_layout')->load($entity_id)) {
      $row_classes = $config_entity->getRowClasses($row_id);
    }

    $form['#prefix'] = '<div id="modal_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    // Row classes textarea.
    $form[Constants::ENTITY_ID] = [
      '#type' => 'hidden',
      '#title' => $this->t('Entity ID'),
      '#default_value' => $entity_id,
      '#disabled' => TRUE,
    ];

    // Row classes textarea.
    $form[Constants::ROW_ID] = [
      '#type' => 'hidden',
      '#title' => $this->t('Row id'),
      '#default_value' => $row_id,
    ];

    // Custom row classes textarea.
    $form['custom_row_classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom row classes'),
      '#description' => $this->t('Fill in your row classes, separated by a comma. E.g: "class1, class2, class3"'),
      '#default_value' => $row_classes,
    ];

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
    }
    else {

      if ($row_classes = $form_state->getValue('row_classes')) {
        // Convert comma separated to array and strip spaces.
        $row_classes = explode(',', $row_classes);
        $row_classes = array_map('trim', $row_classes);

        if (($entity_id = $form_state->getValue(Constants::ENTITY_ID)) &&
          ($config_entity = $this->entityTypeManager->getStorage('dynamic_layout')->load($entity_id))) {

          $row_id = $form_state->getValue(Constants::ROW_ID);

          /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
          $config_entity->setRowClasses($row_id, $row_classes);
          $config_entity->save();
        }
      }

      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['config.dynamic_layouts_modal_form_row'];
  }

}
