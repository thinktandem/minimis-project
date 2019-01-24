<?php

namespace Drupal\dynamic_layouts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\dynamic_layouts\DynamicLayoutSettingsInterface;
use Drupal\dynamic_layouts\DynamicLayoutConstants as Constants;

/**
 * Defines the DynamicLayoutSettings entity.
 *
 * The DynamicLayout entity stores information about a dynamic layout.
 *
 * @ConfigEntityType(
 *   id = "dynamic_layout_settings",
 *   label = @Translation("Dynamic layout settings"),
 *   module = "dynamic_layout",
 *   config_prefix = "dynamic_layout_settings",
 *   admin_permission = "admin dynamic layouts",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\dynamic_layouts\Form\SettingsForm",
 *     },
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/dynamic-layouts/settings/{dynamic_layout_settings}",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   }
 * )
 */
class DynamicLayoutSettings extends ConfigEntityBase implements DynamicLayoutSettingsInterface {

  /**
   * The layout machine name.
   *
   * @var string
   */
  public $id;

  /**
   * The human readable name of this layout.
   *
   * @var string
   */
  public $label;

  /**
   * The regions of this layout.
   *
   * @var string
   */
  public $settings;

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getFrontendLibrary() {
    $settings = unserialize($this->settings);
    return (isset($settings[Constants::FRONTEND_LIBRARY])) ? $settings[Constants::FRONTEND_LIBRARY] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setFrontendLibrary($frontend_library) {
    $settings = unserialize($this->settings);
    $settings[Constants::FRONTEND_LIBRARY] = $frontend_library;
    $this->settings = serialize($settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getColumnPrefix() {
    $settings = unserialize($this->settings);
    $column_prefix = NULL;

    // Check for grid column prefix from settings.
    if (isset($settings[Constants::COLUMN_PREFIX])) {
      $column_prefix = $settings[Constants::COLUMN_PREFIX];
    }

    // Default column prefix for bootstrap.
    if ($this->getFrontendLibrary() == Constants::BOOTSTRAP) {
      $column_prefix = 'col';
    }

    return $column_prefix;
  }

  /**
   * {@inheritdoc}
   */
  public function setColumnPrefix($column_prefix) {
    $settings = unserialize($this->settings);
    $settings[Constants::COLUMN_PREFIX] = $column_prefix;
    $this->settings = serialize($settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getGridColumnCount() {
    $settings = unserialize($this->settings);
    $column_count = NULL;

    // Check for grid column count from settings.
    if (isset($settings[Constants::GRID_COLUMN_COUNT])) {
      $column_count = $settings[Constants::GRID_COLUMN_COUNT];
    }

    // Default column count for bootstrap.
    if ($this->getFrontendLibrary() == Constants::BOOTSTRAP) {
      $column_count = 12;
    }

    return $column_count;
  }

  /**
   * {@inheritdoc}
   */
  public function setGridColumnCount($grid_column_count) {
    $settings = unserialize($this->settings);
    $settings[Constants::GRID_COLUMN_COUNT] = $grid_column_count;
    $this->settings = serialize($settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getFrontendColumnClasses() {
    $frontend_library = $this->getFrontendLibrary();

    // Get the column classes based on the chosen frontend library.
    switch ($frontend_library) {
      case 'custom':
        $column_classes = $this->getCustomColumnClasses();
        break;

      case Constants::BOOTSTRAP:
      default:
        $column_classes = $this->getBootstrapColumnClasses();
        break;
    }

    return $column_classes;
  }

  /**
   * {@inheritdoc}
   */
  public function purgeColumnWidthNumbers($last_column_number, $new_column_prefix='') {
    if (!$layout_config_entities = \Drupal::entityTypeManager()->getStorage('dynamic_layout')->loadMultiple()) {
      return NULL;
    }

    /* @var \Drupal\dynamic_layouts\DynamicLayoutInterface $layout_config_entity */
    foreach ($layout_config_entities as $layout_config_entity) {

      // Loop over the rows and their columns.
      $rows = $layout_config_entity->getRows();
      if ($rows) {
        foreach ($rows as $row_key => $row) {
          $columns = $row[Constants::COLUMNS];
          foreach ($columns as $column_key => $column) {
            $rows[$row_key][Constants::COLUMNS][$column_key]['column_width_number'] = $last_column_number;

            if ($new_column_prefix) {
              $rows[$row_key][Constants::COLUMNS][$column_key]['column_width_prefix'] = $new_column_prefix;
            }

            if (isset($rows[$row_key][Constants::COLUMNS][$column_key]['custom_column_width_number'])) {
              unset($rows[$row_key][Constants::COLUMNS][$column_key]['custom_column_width_number']);
            }
          }
        }
      }

      $layout_config_entity->regions = serialize($rows);
      $layout_config_entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLastColumnNumber($frontend_library, $new_column_prefix, $new_grid_column_count) {
    $last_column_class = '';

    // Get the column classes based on the chosen frontend library.
    switch ($frontend_library) {
      case 'custom':
        $column_classes = $this->getCustomColumnClasses($new_grid_column_count);
        break;

      case Constants::BOOTSTRAP:
      default:
        $column_classes = $this->getBootstrapColumnClasses();
        break;
    }

    if ($column_classes) {
      $last_column_class = end($column_classes);
    }

    return $last_column_class;
  }

  /**
   * {@inheritdoc}
   */
  private function getBootstrapColumnClasses() {
    $bootstrap_column_widths = [];
    for ($i = 1; $i <= 13; $i++) {
      $bootstrap_column_widths[] = $i;
    }
    return $bootstrap_column_widths;
  }

  /**
   * {@inheritdoc}
   */
  private function getCustomColumnClasses($new_grid_column_count = '') {
    $grid_column_count = $this->getGridColumnCount();
    if ($new_grid_column_count) {
      $grid_column_count = $new_grid_column_count;
    }

    // Convert to integer.
    $grid_column_count = intval($grid_column_count);

    $column_classes = [];
    for ($i = 1; $i <= $grid_column_count; $i++) {
      $column_class = $i;
      $column_classes[$column_class] = $column_class;
    }

    return $column_classes;
  }

}
