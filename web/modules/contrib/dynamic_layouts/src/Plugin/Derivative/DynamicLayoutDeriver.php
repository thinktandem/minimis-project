<?php

namespace Drupal\dynamic_layouts\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Makes a flexible layout for each layout config entity.
 */
class DynamicLayoutDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The base plugin ID that the derivative is for.
   *
   * @var string
   */
  protected $basePluginId;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * DynamicLayoutDeriver constructor.
   *
   * @param string $base_plugin_id
   *   The base plugin ID.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entityTypeManager) {
    $this->basePluginId = $base_plugin_id;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $config_entities = $this->entityTypeManager->getStorage('dynamic_layout')
      ->loadMultiple();

    // Now we loop over them and declare the derivatives.
    if ($config_entities) {
      foreach ($config_entities as $entity) {

        /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $entity */
        $regions = $entity->getLayoutRegions();
        $icon_map = $entity->getIconMap();

        $module_path = drupal_get_path('module', 'dynamic_layouts') . '/templates';

        $this->derivatives[$entity->id()] = new LayoutDefinition([
          'label' => $entity->label(),
          'category' => $entity->getCategory(),
          'class' => 'Drupal\dynamic_layouts\Plugin\Layout\DynamicLayout',
          'regions' => $regions,
          'template' => 'dynamic-layout-frontend',
          'path' => $module_path,
          'icon_map' => $icon_map,
          'config_dependencies' => [
            $entity->getConfigDependencyKey() => [
              $entity->getConfigDependencyName(),
            ],
          ],
        ]);
      }
    }

    return $this->derivatives;
  }

}
