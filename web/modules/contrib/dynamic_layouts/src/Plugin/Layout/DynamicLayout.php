<?php

namespace Drupal\dynamic_layouts\Plugin\Layout;

use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Dynamically prepare the dynamic layout build.
 *
 * @Layout(
 *   id = "dynamic_layout",
 *   admin_label = @Translation("Dynamic layout"),
 *   category = @Translation("Dynamic layout category"),
 *   deriver = "Drupal\dynamic_layouts\Plugin\Derivative\DynamicLayoutDeriver"
 * )
 */
class DynamicLayout extends LayoutDefault implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\dynamic_layouts\Entity\DynamicLayoutSettings
   */
  protected $settings;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->settings = $this->entityTypeManager->getStorage('dynamic_layout_settings')->load('settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {

    // Get the layout config entity type & entity id from the plugin id.
    $plugin_id = $this->getPluginId();
    list($entity_type, $entity_id) = explode(':', $plugin_id);

    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $config_entity */
    $rows = [];
    if ($config_entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id)) {
      $rows = $config_entity->getRows();
    }

    // Ensure $build only contains defined regions and in the order defined.
    $build = [];
    foreach ($this->getPluginDefinition()->getRegionNames() as $region_name) {
      if (array_key_exists($region_name, $regions)) {
        $build[$region_name] = $regions[$region_name];
      }
    }

    $build['#settings'] = $this->getConfiguration();
    $build['#layout'] = $this->pluginDefinition;
    $build['#wrapperClasses'] = $this->getWrapperClasses();
    $build['#theme'] = $this->pluginDefinition->getThemeHook();
    if ($library = $this->pluginDefinition->getLibrary()) {
      $build['#attached']['library'][] = $library;
    }

    if ($this->settings->getFrontendLibrary() == 'custom') {
      $build['#attached']['library'][] = 'dynamic_layouts/dynamic_layouts_frontend';
    }

    $build['rows'] = [
      '#markup' => $rows,
    ];

    return $build;
  }

  /**
   * Get all the wrapper classes.
   */
  private function getWrapperClasses() {
    $frontend_library = $this->settings->getFrontendLibrary();
    if ($column_count = $this->settings->getGridColumnCount()) {
      return $frontend_library . '-' . $column_count;
    }

    return '';
  }

}
