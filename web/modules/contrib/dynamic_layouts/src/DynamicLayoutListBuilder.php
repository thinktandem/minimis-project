<?php

namespace Drupal\dynamic_layouts;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of user Dynamic Layouts.
 *
 * @see \Drupal\dynamic_layouts\Entity\DynamicLayout
 */
class DynamicLayoutListBuilder extends EntityListBuilder {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Constructs a new LanguageListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage handler class.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($entity_type, $storage);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dynamic_layout_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    if (($settings = $this->entityTypeManager->getStorage('dynamic_layout_settings')->load('settings')) && (!$settings->getFrontendLibrary())) {
      $settings_link = Link::fromTextAndUrl(t('here'), Url::fromRoute('dynamic_layout.dynamic_layout_settings'))->toString();

      // Display a message.
      drupal_set_message(t('Before creating a layout, please configure your settings @link!', array('@link' => $settings_link)), 'warning');
    }

    $header['label'] = t('Name');
    $header['category'] = t('Category');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\dynamic_layouts\Entity\DynamicLayout $entity */
    $row['label'] = $entity->label();
    $row['category'] = Html::escape($entity->getCategory());
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = array(
        'title' => t('Edit layout'),
        'weight' => 20,
        'url' => $entity->toUrl('edit-form'),
      );
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    drupal_set_message(t('The dynamic layout settings have been updated.'));
  }

}
