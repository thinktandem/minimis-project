<?php

namespace Drupal\dynamic_layouts;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface for DynamicLayout.
 */
interface DynamicLayoutInterface extends ConfigEntityInterface {

  /**
   * Get the layout category.
   */
  public function getCategory();

  /**
   * Get the layout regions.
   */
  public function getRegions();

  /**
   * Get all layout regions.
   */
  public function getLayoutRegions();

  /**
   * Set the layout row classes.
   *
   * @param int $row_id
   *   The row number we need to set.
   * @param array $row_classes
   *   The row classes we need to set.
   */
  public function setRowClasses($row_id, array $row_classes);

  /**
   * Set the layout column classes.
   *
   * @param int $row_id
   *   The row number we need to set the classes on.
   * @param int $column_id
   *   The column id we need to set the classes on.
   * @param array $column_classes
   *   The column classes.
   */
  public function setCustomColumnClasses($row_id, $column_id, array $column_classes);

  /**
   * Set the layout column classes.
   *
   * @param int $row_id
   *   The row number we need to set the classes on.
   * @param int $column_id
   *   The column id we need to set the classes on.
   * @param string $column_name
   *   The column name.
   */
  public function setColumnName($row_id, $column_id, $column_name);

  /**
   * Set the custom column width number.
   *
   * @param int $row_id
   *   The row number we need to set the classes on.
   * @param int $column_id
   *   The column id we need to set the classes on.
   * @param string $column_width_number
   *   The column width number.
   */
  public function setCustomColumnWidthNumber($row_id, $column_id, $column_width_number);

  /**
   * Set the column width number.
   *
   * @param int $row_id
   *   The row number we need to set the classes on.
   * @param int $column_id
   *   The column id we need to set the classes on.
   */
  public function getColumnWidthNumber($row_id, $column_id);

  /**
   * Get the layout row classes, comma separated.
   *
   * @param int $row_id
   *   The row number we need to get.
   */
  public function getRowClasses($row_id);

  /**
   * Get the layout column classes, comma separated.
   *
   * @param int $row_id
   *   The row number we need to get the column classes from.
   * @param int $column_id
   *   The column id we need to get.
   */
  public function getColumnClasses($row_id, $column_id);

  /**
   * Get the layout column name.
   *
   * @param int $row_id
   *   The row number we need to get the column name from.
   * @param int $column_id
   *   The column id we need to get.
   */
  public function getColumnName($row_id, $column_id);

  /**
   * Delete a specific row from the layout.
   *
   * @param int $row_id
   *   The row number we need to delete.
   */
  public function deleteRow($row_id);

  /**
   * Delete a specific column from the layout.
   *
   * @param int $row_id
   *   The row number we need to delete the column from.
   * @param int $column_id
   *   The column id we need to delete.
   */
  public function deleteColumn($row_id, $column_id);

  /**
   * Add a row to the layout.
   */
  public function addRow();

  /**
   * Add multiple rows to the layout.
   *
   * @param int $rows_count
   *   The amount of rows to add.
   */
  public function addStartingRows($rows_count);

  /**
   * Add a column to a row.
   *
   * @param int $row_id
   *   The row number we need to add the column to.
   */
  public function addColumn($row_id);

  /**
   * Get the unserialized rows.
   */
  public function getRows();

  /**
   * Get the default column class.
   */
  public function getDefaultColumnClass();

  /**
   * Get the default row class.
   */
  public function getDefaultRowClass();

  /**
   * Get the icon map for this layout.
   */
  public function getIconMap();

  /**
   * Set the default column class.
   *
   * @param string $default_column_class
   *   The default column class we need to set.
   */
  public function setDefaultColumnClass($default_column_class);

  /**
   * Set the default row class.
   *
   * @param string $default_row_class
   *   The default row class we need to set.
   */
  public function setDefaultRowClass($default_row_class);

}
