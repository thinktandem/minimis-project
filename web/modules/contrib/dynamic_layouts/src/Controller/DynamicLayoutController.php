<?php

namespace Drupal\dynamic_layouts\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Access\AccessResult;
use Drupal\dynamic_layouts\DynamicLayoutConstants as Constants;

/**
 * DynamicLayoutController class.
 */
class DynamicLayoutController extends ControllerBase {
  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

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
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder, EntityTypeManagerInterface $entityTypeManager, FormBuilder $formBuilder) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFormBuilder = $entity_form_builder;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.form_builder'),
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  /**
   * Checks access to add a new layout.
   */
  public function access() {
    $settings_created = FALSE;

    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    if (($settings = $this->entityTypeManager->getStorage('dynamic_layout_settings')->load('settings')) && ($settings->getFrontendLibrary())) {
      $settings_created = TRUE;
    }

    return AccessResult::allowedIf($settings_created);
  }

  /**
   * Callback for deleting a column.
   *
   * @param string $dynamic_layout_id
   *   The dynamic layout id.
   * @param int $column_id
   *   The column id we need to delete.
   * @param int $row_id
   *   The row number we need to delete the column.
   *
   * @return object
   *   The ajax response.
   */
  public function deleteColumn($dynamic_layout_id, $column_id, $row_id) {
    $response = new AjaxResponse();

    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    if ($config_entity = $this->entityTypeManager->getStorage(Constants::DYNAMIC_LAYOUT)->load($dynamic_layout_id)) {

      // Delete the specific column from the config entity.
      $config_entity->deleteColumn($row_id, $column_id);

      // Save the entity.
      $config_entity->save();

      // Replace the layout form with newly updated values.
      /* @var \Drupal\dynamic_layouts\Form\DynamicLayoutForm $layout_form */
      $layout_form = $this->entityFormBuilder->getForm($config_entity);

      $response->addCommand(new ReplaceCommand(Constants::DYNAMIC_LAYOUT_FORM_CLASS, $layout_form));
    }

    return $response;
  }

  /**
   * Callback for adding a column.
   *
   * @param string $dynamic_layout_id
   *   The dynamic layout id.
   * @param int $row_id
   *   The row number we need to delete the column.
   *
   * @return object
   *   The ajax response.
   */
  public function addColumn($dynamic_layout_id, $row_id) {
    $response = new AjaxResponse();

    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    if ($config_entity = $this->entityTypeManager->getStorage(Constants::DYNAMIC_LAYOUT)->load($dynamic_layout_id)) {

      // Add a column to the config entity.
      $config_entity->addColumn($row_id);

      // Save the entity.
      $config_entity->save();

      // Replace the layout form with newly updated values.
      /* @var \Drupal\dynamic_layouts\Form\DynamicLayoutForm $layout_form */
      $layout_form = $this->entityFormBuilder->getForm($config_entity);

      $response->addCommand(new ReplaceCommand(Constants::DYNAMIC_LAYOUT_FORM_CLASS, $layout_form));
    }

    return $response;
  }

  /**
   * Callback for opening the modal form.
   *
   * @param string $dynamic_layout_id
   *   The dynamic layout id.
   * @param int $row_id
   *   The row number we need to delete.
   *
   * @return object
   *   The ajax response.
   */
  public function deleteRow($dynamic_layout_id, $row_id) {
    $response = new AjaxResponse();

    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    if ($config_entity = $this->entityTypeManager->getStorage(Constants::DYNAMIC_LAYOUT)->load($dynamic_layout_id)) {

      // Delete the specific row from the config entity.
      $config_entity->deleteRow($row_id);

      // Save the entity.
      $config_entity->save();

      // Replace the layout form with newly updated values.
      /* @var \Drupal\dynamic_layouts\Form\DynamicLayoutForm $layout_form */
      $layout_form = $this->entityFormBuilder->getForm($config_entity);

      $response->addCommand(new ReplaceCommand(Constants::DYNAMIC_LAYOUT_FORM_CLASS, $layout_form));
    }

    return $response;
  }

  /**
   * Callback for adding a row.
   *
   * @param string $dynamic_layout_id
   *   The dynamic layout id.
   *
   * @return object
   *   The ajax response.
   */
  public function addRow($dynamic_layout_id) {
    $response = new AjaxResponse();

    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    if ($config_entity = $this->entityTypeManager->getStorage(Constants::DYNAMIC_LAYOUT)->load($dynamic_layout_id)) {

      // Add a row.
      $config_entity->addRow();

      // Save the entity.
      $config_entity->save();

      // Replace the layout form with newly updated values.
      /* @var \Drupal\dynamic_layouts\Form\DynamicLayoutForm $layout_form */
      $layout_form = $this->entityFormBuilder->getForm($config_entity);

      $response->addCommand(new ReplaceCommand(Constants::DYNAMIC_LAYOUT_FORM_CLASS, $layout_form));
    }

    return $response;
  }

  /**
   * Callback for opening the edit column modal form.
   */
  public function openEditColumnModalForm() {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $modal_form = $this->formBuilder->getForm('Drupal\dynamic_layouts\Form\EditColumnModalForm');

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('Edit column', $modal_form, ['width' => '800']));

    return $response;
  }

  /**
   * Callback for opening the modal form.
   */
  public function openEditRowModalForm() {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $modal_form = $this->formBuilder->getForm('Drupal\dynamic_layouts\Form\EditRowModalForm');

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('Edit row', $modal_form, ['width' => '800']));

    return $response;
  }

}
