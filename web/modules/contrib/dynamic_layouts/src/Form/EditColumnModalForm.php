<?php

namespace Drupal\dynamic_layouts\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\dynamic_layouts\DynamicLayoutConstants as Constants;

/**
 * EditColumnModalForm class.
 */
class EditColumnModalForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * EditRowModalForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'edit_column_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $entity_id = \Drupal::request()->get(Constants::ENTITY_ID);
    $column_id = \Drupal::request()->get(Constants::COLUMN_ID);
    $row_id = \Drupal::request()->get(Constants::ROW_ID);

    $column_classes = '';
    $column_name = '';
    $column_width_number = '';
    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    if ($config_entity = $this->entityTypeManager->getStorage('dynamic_layout')->load($entity_id)) {
      $column_classes = $config_entity->getColumnClasses($row_id, $column_id);
      $column_name = $config_entity->getColumnName($row_id, $column_id);
      $column_width_number = $config_entity->getColumnWidthNumber($row_id, $column_id);
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

    // Row id disabled textfield.
    $form[Constants::ROW_ID] = [
      '#type' => 'hidden',
      '#title' => $this->t('Row id'),
      '#default_value' => $row_id,
      '#disabled' => TRUE,
    ];

    // Column id disabled textfield.
    $form[Constants::COLUMN_ID] = [
      '#type' => 'hidden',
      '#title' => $this->t('Column id'),
      '#default_value' => $column_id,
      '#disabled' => TRUE,
    ];

    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    if ($settings = $this->entityTypeManager->getStorage('dynamic_layout_settings')->load('settings')) {
      $column_class_options = $settings->getFrontendColumnClasses();

      // Column classes textarea.
      $form['column_width_number'] = [
        '#type' => 'select',
        '#title' => $this->t('Column width class'),
        '#description' => $this->t('Choose your class for the width of this column, these classes are based on the Dynamic Layouts settings.'),
        '#options' => $column_class_options,
        '#default_value' => $column_width_number,
      ];
    }

    // Column name textfield.
    $form['column_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Column name'),
      '#default_value' => $column_name,
    ];

    // Column classes textarea.
    $form['custom_column_classes'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Custom column classes'),
      '#description' => $this->t('Fill in your column classes, separated by a comma. E.g: "class1, class2, class3"'),
      '#default_value' => $column_classes,
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
      $response->addCommand(new ReplaceCommand('#edit_column_modal_form', $form));
    }
    else {

      $row_id = $form_state->getValue(Constants::ROW_ID);
      $column_id = $form_state->getValue(Constants::COLUMN_ID);

      if ($entity_id = $form_state->getValue(Constants::ENTITY_ID)) {

        $this->updateValues($entity_id, $row_id, $column_id, $form_state, $response);
      }

      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * Update the entity values.
   *
   * @param int $entity_id
   *   The entity id.
   * @param int $row_id
   *   The row id.
   * @param int $column_id
   *   The column id.
   * @param object $form_state
   *   The form state.
   * @param object $response
   *   The ajax response.
   */
  public function updateValues($entity_id, $row_id, $column_id, $form_state, $response) {
    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    if ($config_entity = $this->entityTypeManager->getStorage('dynamic_layout')->load($entity_id)) {

      // Set the column classes.
      if ($column_classes = $form_state->getValue('custom_column_classes')) {
        // Convert comma separated to array & trim spaces.
        $column_classes = array_map('trim', array_filter(explode(',', $column_classes)));

        $config_entity->setCustomColumnClasses($row_id, $column_id, $column_classes);
      }

      // Set the column name.
      $column_name = $form_state->getValue('column_name');
      $config_entity->setColumnName($row_id, $column_id, $column_name);

      // Set the column width number.
      if ($new_column_width_number = $form_state->getValue('column_width_number')) {
        $old_column_width_number = $config_entity->getColumnWidthNumber($row_id, $column_id);
        if ($new_column_width_number != $old_column_width_number) {
          $config_entity->setCustomColumnWidthNumber($row_id, $column_id, $new_column_width_number);
        }
      }

      // Save the config entity.
      $config_entity->save();

      // Replace the layout form with newly updated values.
      /* @var \Drupal\dynamic_layouts\Form\DynamicLayoutForm $layout_form */
      $layout_form = $this->entityFormBuilder->getForm($config_entity);

      $response->addCommand(new ReplaceCommand('.dynamic-layout-form', $layout_form));
    }
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
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.dynamic_layouts_modal_form_column'];
  }

}
