<?php

namespace Drupal\dynamic_layouts\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\dynamic_layouts\DynamicLayoutInterface;
use Drupal\dynamic_layouts\DynamicLayoutSettingsInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\dynamic_layouts\DynamicLayoutConstants as Constants;

/**
 * Defines the DynamicLayout entity.
 *
 * The DynamicLayout entity stores information about a dynamic layout.
 *
 * @ConfigEntityType(
 *   id = "dynamic_layout",
 *   label = @Translation("Dynamic Layout"),
 *   module = "dynamic_layout",
 *   config_prefix = "dynamic_layout",
 *   admin_permission = "admin dynamic layouts",
 *   handlers = {
 *     "list_builder" = "Drupal\dynamic_layouts\DynamicLayoutListBuilder",
 *     "form" = {
 *       "default" = "Drupal\dynamic_layouts\Form\DynamicLayoutForm",
 *       "delete" = "Drupal\dynamic_layouts\Form\DynamicLayoutDeleteForm"
 *     },
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/dynamic-layout/manage/{dynamic_layout}",
 *     "delete-form" =
 *   "/admin/config/dynamic-layout/manage/{dynamic_layout}/delete"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "weight" = "weight"
 *   }
 * )
 */
class DynamicLayout extends ConfigEntityBase implements DynamicLayoutInterface {

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
   * The position weight (not physical) of this layout.
   *
   * @var int
   */
  public $weight;

  /**
   * The category of this layout.
   *
   * @var string
   */
  public $category;

  /**
   * The regions of this layout.
   *
   * @var string
   */
  public $regions;

  /**
   * The default column class.
   *
   * @var string
   */
  public $default_column_class;

  /**
   * The default row class.
   *
   * @var string
   */
  public $default_row_class;

  /**
   * {@inheritdoc}
   */
  public function getCategory() {
    return $this->category;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultColumnClass() {
    return $this->default_column_class;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultRowClass($default_row_class) {
    $this->default_row_class = $default_row_class;

    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    if ($rows) {
      foreach ($rows as $key => $row) {
        $rows[$key][Constants::DEFAULT_ROW_CLASS] = $default_row_class;
      }
    }

    $this->regions = serialize($rows);

  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultColumnClass($default_column_class) {
    $this->default_column_class = $default_column_class;

    // Loop over the rows and their columns.
    $rows = $this->getRows();
    if ($rows) {
      foreach ($rows as $row_key => $row) {
        $columns = $row[Constants::COLUMNS];
        foreach ($columns as $column_key => $column) {
          $rows[$row_key][Constants::COLUMNS][$column_key][Constants::DEFAULT_COLUMN_CLASS] = $default_column_class;
        }
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultRowClass() {
    return $this->default_row_class;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegions() {
    return $this->regions;
  }

  /**
   * {@inheritdoc}
   */
  public function setRowClasses($row_id, array $row_classes) {
    $updated_row = $this->getRowById($row_id);
    $updated_row[Constants::CUSTOM_ROW_CLASSES] = $row_classes;

    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    foreach ($rows as $key => $row) {
      if ($row[Constants::ROW_ID] == $row_id) {
        $rows[$key] = $updated_row;
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomColumnClasses($row_id, $column_id, array $column_classes) {
    $updated_row = $this->getRowById($row_id);
    $updated_column = $this->getColumnById($updated_row, $column_id);
    $updated_column[Constants::CUSTOM_COLUMN_CLASSES] = $column_classes;
    $columns = $updated_row[Constants::COLUMNS];

    // Loop over the columns and set the updated column.
    foreach ($columns as $key => $column) {
      if ($column[Constants::COLUMN_ID] == $column_id) {
        $columns[$key] = $updated_column;
      }
    }

    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    foreach ($rows as $key => $row) {
      if ($row[Constants::ROW_ID] == $row_id) {
        $rows[$key][Constants::COLUMNS] = $columns;
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function setColumnName($row_id, $column_id, $column_name) {
    $updated_row = $this->getRowById($row_id);
    $columns = $updated_row[Constants::COLUMNS];

    // Set the column name.
    $updated_column = $this->getColumnById($updated_row, $column_id);
    $updated_column[Constants::COLUMN_NAME] = $column_name;

    if (!$column_name) {
      $column_name = 'r' . $row_id . 'c' . $column_id;
    }

    // Convert to machine name.
    $region_name = strtolower($column_name);
    $region_name = preg_replace('/[^a-z0-9_]+/', '_', $region_name);
    $region_name = preg_replace('/_+/', '_', $region_name);

    // Set the region name.
    $updated_column[Constants::REGION_NAME] = $region_name;

    // Loop over the columns and set the updated column.
    foreach ($columns as $key => $column) {
      if ($column[Constants::COLUMN_ID] == $column_id) {
        $columns[$key] = $updated_column;
      }
    }

    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    foreach ($rows as $key => $row) {
      if ($row[Constants::ROW_ID] == $row_id) {
        $rows[$key][Constants::COLUMNS] = $columns;
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function setCustomColumnWidthNumber($row_id, $column_id, $custom_column_width_number) {
    $updated_row = $this->getRowById($row_id);
    $updated_column = $this->getColumnById($updated_row, $column_id);
    $updated_column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER] = $custom_column_width_number;
    $columns = $updated_row[Constants::COLUMNS];

    // Loop over the columns and set the updated column.
    foreach ($columns as $key => $column) {
      if ($column[Constants::COLUMN_ID] == $column_id) {
        $columns[$key] = $updated_column;
      }
    }

    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    foreach ($rows as $key => $row) {
      if ($row[Constants::ROW_ID] == $row_id) {
        $rows[$key][Constants::COLUMNS] = $columns;
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function getRowClasses($row_id) {
    $row = $this->getRowById($row_id);

    // Convert to comma separated.
    $row_classes = '';
    if (is_array($row[Constants::CUSTOM_ROW_CLASSES])) {
      $row_classes = implode(', ', $row[Constants::CUSTOM_ROW_CLASSES]);
    }

    return $row_classes;
  }

  /**
   * {@inheritdoc}
   */
  public function getColumnClasses($row_id, $column_id) {
    $row = $this->getRowById($row_id);
    $column_classes = '';

    $column = $this->getColumnById($row, $column_id);

    // Guarding.
    if (!isset($column[Constants::CUSTOM_COLUMN_CLASSES])) {
      return $column_classes;
    }
    if (!is_array($column[Constants::CUSTOM_COLUMN_CLASSES])) {
      return $column_classes;
    }

    return implode(', ', $column[Constants::CUSTOM_COLUMN_CLASSES]);
  }

  /**
   * {@inheritdoc}
   */
  public function getColumnName($row_id, $column_id) {
    $row = $this->getRowById($row_id);
    $column = $this->getColumnById($row, $column_id);
    return $column[Constants::COLUMN_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function getColumnWidthNumber($row_id, $column_id) {
    $row = $this->getRowById($row_id);
    $column = $this->getColumnById($row, $column_id);

    $column_width_number = $column[Constants::COLUMN_WIDTH_NUMBER];
    if (isset($column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER])) {
      $column_width_number = $column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER];
    }

    return $column_width_number;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRow($row_id) {
    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    foreach ($rows as $key => $row) {
      if ($row[Constants::ROW_ID] == $row_id) {
        unset($rows[$key]);
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function addRow() {
    $rows = $this->getRows();
    $column_count = 1;

    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    $last_column_class = '';
    $column_width_prefix = '';
    if ($settings = \Drupal::entityTypeManager()
      ->getStorage(Constants::DYNAMIC_LAYOUT_SETTINGS)
      ->load(Constants::SETTINGS)) {
      $column_classes = array_keys($settings->getFrontendColumnClasses());
      $last_column_class = end($column_classes);
      $column_width_prefix = $settings->getColumnPrefix();
    }

    $row_id = uniqid();

    $columns = [];
    for ($i = 1; $i <= $column_count; $i++) {

      $column_id = uniqid();

      $edit_column = $this->getEditColumnLink($this->id(), $row_id, $column_id);
      $delete_column = $this->getDeleteColumnLink($this->id(), $row_id, $column_id);

      $columns[] = [
        Constants::EDIT_COLUMN => $edit_column,
        Constants::DELETE_COLUMN => $delete_column,
        Constants::COLUMN_ID => $column_id,
        Constants::COLUMN_WIDTH_NUMBER => $last_column_class,
        Constants::COLUMN_WIDTH_PREFIX => $column_width_prefix,
        Constants::DEFAULT_COLUMN_CLASS => $this->default_column_class,
        Constants::CUSTOM_COLUMN_CLASSES => [],
        Constants::COLUMN_NAME => '',
        Constants::REGION_NAME => 'r' . $row_id . 'c' . $column_id,
        Constants::ADMIN_COLUMN_CLASSES => [
          Constants::DYNAMIC_LAYOUT_COLUMN,
        ],
      ];
    }

    // Add new row.
    $rows[] = [
      Constants::ROW_ID => $row_id,
      'admin_row_classes' => ['dynamic-layout-row'],
      Constants::DEFAULT_ROW_CLASS => $this->default_row_class,
      Constants::CUSTOM_ROW_CLASSES => [],
      Constants::COLUMNS => $columns,
    ];

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function addStartingRows($general_settings) {
    $rows_count = 1;
    if (isset($general_settings['start_rows_count'])) {
      $rows_count = $general_settings['start_rows_count'];
    }

    // Set the default column class for the starting rows.
    $starting_default_column_class = '';
    if (isset($general_settings[Constants::DEFAULT_COLUMN_CLASS]) && $general_settings[Constants::DEFAULT_COLUMN_CLASS]) {
      $starting_default_column_class = \trim($general_settings[Constants::DEFAULT_COLUMN_CLASS]);
      $this->default_column_class = $starting_default_column_class;
    }

    // Set the default row class for the starting rows.
    $starting_default_row_class = '';
    if (isset($general_settings[Constants::DEFAULT_ROW_CLASS]) && $general_settings[Constants::DEFAULT_ROW_CLASS]) {
      $starting_default_row_class = \trim($general_settings[Constants::DEFAULT_ROW_CLASS]);
      $this->default_row_class = $starting_default_row_class;
    }

    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    $last_column_class = '';
    $column_width_prefix = '';
    if ($settings = \Drupal::entityTypeManager()
      ->getStorage(Constants::DYNAMIC_LAYOUT_SETTINGS)
      ->load(Constants::SETTINGS)) {
      $column_width_prefix = $settings->getColumnPrefix();
      $column_classes = array_keys($settings->getFrontendColumnClasses());
      $last_column_class = end($column_classes);
    }

    // Add new rows.
    $rows = [];
    for ($i = 1; $i <= $rows_count; $i++) {
      $row_id = uniqid();
      $column_id = uniqid();
      $columns = [];

      $edit_column = $this->getEditColumnLink($this->id(), $row_id, $column_id);
      $delete_column = $this->getDeleteColumnLink($this->id(), $row_id, $column_id);

      $columns[] = [
        Constants::EDIT_COLUMN => $edit_column,
        Constants::DELETE_COLUMN => $delete_column,
        Constants::COLUMN_ID => $column_id,
        Constants::COLUMN_WIDTH_NUMBER => $last_column_class,
        Constants::COLUMN_WIDTH_PREFIX => $column_width_prefix,
        Constants::DEFAULT_COLUMN_CLASS => $starting_default_column_class,
        Constants::CUSTOM_COLUMN_CLASSES => [],
        Constants::COLUMN_NAME => '',
        Constants::REGION_NAME => 'r' . $row_id . 'c' . $column_id,
        Constants::ADMIN_COLUMN_CLASSES => [
          Constants::DYNAMIC_LAYOUT_COLUMN,
        ],
      ];

      $rows[] = [
        Constants::ROW_ID => $row_id,
        Constants::DEFAULT_ROW_CLASS => $starting_default_row_class,
        'admin_row_classes' => ['dynamic-layout-row'],
        Constants::CUSTOM_ROW_CLASSES => [],
        Constants::COLUMNS => $columns,
      ];
    }

    $this->regions = serialize($rows);
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  private function getDeleteColumnLink($entity_id, $row_id, $column_id) {
    $delete_column_url = Url::fromRoute(
      'dynamic_layouts.delete_column',
      [
        Constants::COLUMN_ID => $column_id,
        'dynamic_layout_id' => $entity_id,
        Constants::ROW_ID => $row_id,
      ]
    );

    $title = t('Delete column');

    $link_options = [
      'attributes' => [
        'class' => [
          'delete-link',
          'btn',
          'use-ajax',
        ],
        'title' => $title,
      ],
    ];
    $delete_column_url->setOptions($link_options);

    $delete_column_link = Link::fromTextAndUrl($title, $delete_column_url);
    $delete_column = $delete_column_link->toRenderable();

    return render($delete_column);
  }

  /**
   * {@inheritdoc}
   */
  private function getEditColumnLink($entity_id, $row_id, $column_id) {
    $edit_column_url = Url::fromRoute(
      'dynamic_layouts.edit_column_modal_form',
      [
        Constants::COLUMN_ID => $column_id,
        'entity_id' => $entity_id,
        Constants::ROW_ID => $row_id,
      ]
    );

    $title = t('Edit column');

    $link_options = [
      'attributes' => [
        'class' => [
          'edit-link',
          'btn',
          'use-ajax',
        ],
        'title' => $title,
      ],
    ];
    $edit_column_url->setOptions($link_options);

    $edit_column_link = Link::fromTextAndUrl($title, $edit_column_url);
    $edit_column = $edit_column_link->toRenderable();

    return render($edit_column);
  }

  /**
   * Render a link to add a column to a row.
   *
   * @param int $row_id
   *   The specific row id.
   * @param string $route
   *   The route we need to render the link to.
   * @param string $text
   *   The text we want to render in the link.
   * @param array $options
   *   Give options to the link.
   *
   * @return string
   *   The rendered link.
   */
  public function getRowLink($row_id, $route, $text, array $options = []) {
    $link = Link::createFromRoute($text, $route, ['dynamic_layout_id' => $this->id(), Constants::ROW_ID => $row_id], $options);
    $renderLink = $link->toRenderable();
    return render($renderLink);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteColumn($row_id, $column_id) {
    $updated_row = $this->getRowById($row_id);
    $columns = $updated_row[Constants::COLUMNS];

    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    $calculated_column_width_number = NULL;
    if ($settings = \Drupal::entityTypeManager()
      ->getStorage(Constants::DYNAMIC_LAYOUT_SETTINGS)
      ->load(Constants::SETTINGS)) {
      $calculated_column_width_number = $this->calculateColumnWidth($columns, $settings, 'deleted');
    }

    // Loop over the columns and set the updated column.
    foreach ($columns as $key => $column) {
      if ($column[Constants::COLUMN_ID] == $column_id) {
        unset($columns[$key]);
        continue;
      }
      if (($calculated_column_width_number) && (!isset($column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER]))) {
        $columns[$key][Constants::COLUMN_WIDTH_NUMBER] = $calculated_column_width_number;
      }
    }

    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    foreach ($rows as $key => $row) {
      if ($row[Constants::ROW_ID] == $row_id) {
        $rows[$key][Constants::COLUMNS] = $columns;
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  public function addColumn($row_id) {
    $updated_row = $this->getRowById($row_id);
    $columns = $updated_row[Constants::COLUMNS];

    $column_id = uniqid();
    $column_width_number = '';

    $edit_column = $this->getEditColumnLink($this->id(), $row_id, $column_id);
    $delete_column = $this->getDeleteColumnLink($this->id(), $row_id, $column_id);

    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    $column_width_prefix = '';
    if ($settings = \Drupal::entityTypeManager()
      ->getStorage(Constants::DYNAMIC_LAYOUT_SETTINGS)
      ->load(Constants::SETTINGS)) {
      $column_classes = $settings->getFrontendColumnClasses();
      $column_width_number = end($column_classes);
      $column_width_prefix = $settings->getColumnPrefix();

      if ($calculated_column_width_number = $this->calculateColumnWidth($columns, $settings, 'added')) {
        $column_width_number = $calculated_column_width_number;

        // Give each column this column width number.
        foreach ($columns as $key => $column) {
          if (!isset($column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER])) {
            $columns[$key][Constants::COLUMN_WIDTH_NUMBER] = $calculated_column_width_number;
          }
        }
      }
    }

    $columns[] = [
      Constants::EDIT_COLUMN => $edit_column,
      Constants::DELETE_COLUMN => $delete_column,
      Constants::COLUMN_ID => $column_id,
      Constants::COLUMN_WIDTH_NUMBER => $column_width_number,
      Constants::COLUMN_WIDTH_PREFIX => $column_width_prefix,
      Constants::DEFAULT_COLUMN_CLASS => $this->default_column_class,
      Constants::CUSTOM_COLUMN_CLASSES => [],
      Constants::COLUMN_NAME => '',
      Constants::REGION_NAME => 'r' . $row_id . 'c' . $column_id,
      Constants::ADMIN_COLUMN_CLASSES => [
        Constants::DYNAMIC_LAYOUT_COLUMN,
      ],
    ];

    // Loop over the rows and find the corresponding row number.
    $rows = $this->getRows();
    foreach ($rows as $key => $row) {
      if ($row[Constants::ROW_ID] == $row_id) {
        $rows[$key][Constants::COLUMNS] = $columns;
      }
    }

    $this->regions = serialize($rows);
  }

  /**
   * {@inheritdoc}
   */
  private function calculateColumnWidth($columns, DynamicLayoutSettingsInterface $settings, $action) {
    if (!$settings) {
      return NULL;
    }
    if (!$columns) {
      return NULL;
    }

    // Count the column's that dont have a custom column width number.
    $column_counter = 0;
    foreach ($columns as $column) {
      if (!isset($column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER])) {
        $column_counter++;
      }
    }

    // Get the frontend library & column count and add/remove 1.
    $column_count = $column_counter + 1;
    if ($action == 'deleted') {
      $column_count = $column_counter - 1;
    }

    // Convert to integer, just to be sure.
    $grid_column_count = \intval($settings->getGridColumnCount());

    // Divide the column count by grid column count.
    return \round($grid_column_count / $column_count);
  }

  /**
   * {@inheritdoc}
   */
  public function getRows() {
    return unserialize($this->regions);
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutRegions() {
    $rows = $this->getRows();

    // Loop over the rows to gather the layout regions.
    $layout_regions = [];
    $row_count = 1;
    foreach ($rows as $row) {
      // Check if we have columns.
      if ((!isset($row[Constants::ROW_ID])) || (!isset($row[Constants::COLUMNS]))) {
        continue;
      }

      // Loop over the columns from this row.
      $column_count = 1;
      foreach ($row[Constants::COLUMNS] as $column) {
        $region_name = t('Row @row_count - Column @column_count',
          [
            '@row_count' => $row_count,
            '@column_count' => $column_count,
          ]
        );
        $region_machine_name = 'r' . $row[Constants::ROW_ID] . 'c' . $column[Constants::COLUMN_ID];
        if (isset($column[Constants::COLUMN_NAME]) && $column[Constants::COLUMN_NAME]) {
          $region_name = $column[Constants::COLUMN_NAME];

          // Convert to machine name.
          $region_machine_name = strtolower($region_name);
          $region_machine_name = preg_replace('/[^a-z0-9_]+/', '_', $region_machine_name);
          $region_machine_name = preg_replace('/_+/', '_', $region_machine_name);

          // Overwrite if we have a region name.
          if (isset($column[Constants::REGION_NAME]) && $column[Constants::REGION_NAME]) {
            $region_machine_name = $column[Constants::REGION_NAME];
          }
        }

        $layout_regions[$region_machine_name] = [
          'label' => $region_name,
        ];
        $column_count++;
      }
      $row_count++;
    }

    return $layout_regions;
  }

  /**
   * {@inheritdoc}
   */
  private function getRowById($row_id) {
    $rows = $this->getRows();
    $row_by_number = [];

    // Loop over the rows.
    foreach ($rows as $row) {
      if ($row[Constants::ROW_ID] === $row_id) {
        $row_by_number = $row;
      }
    }

    return $row_by_number;
  }

  /**
   * {@inheritdoc}
   */
  private function getColumnById($row, $column_id) {
    $column_by_id = [];

    // Loop over the row columns.
    foreach ($row[Constants::COLUMNS] as $column) {
      if ($column[Constants::COLUMN_ID] === $column_id) {
        $column_by_id = $column;
      }
    }

    return $column_by_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getIconMap() {
    $icon_map = [];

    $rows = $this->getRows();
    foreach($rows as $key =>$row) {

      $icon_map[$key] = [];
      if (isset($row[Constants::COLUMNS]) && !empty(Constants::COLUMNS)) {
        $columns = $row[Constants::COLUMNS];

        foreach($columns as $column) {
          $column_width_number = $column[Constants::COLUMN_WIDTH_NUMBER];

          if ((isset($column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER])) && (!empty($column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER]))) {
            $column_width_number = $column[Constants::CUSTOM_COLUMN_WIDTH_NUMBER];
          }

          for ($i = 1; $i <= $column_width_number; $i++) {
            $icon_map[$key][] = $column[Constants::COLUMN_ID];
          }
        }

      }
    }

    return array_values($icon_map);
  }

}
