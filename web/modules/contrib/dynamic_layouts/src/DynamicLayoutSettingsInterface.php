<?php

namespace Drupal\dynamic_layouts;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface for DynamicLayout.
 */
interface DynamicLayoutSettingsInterface extends ConfigEntityInterface {

  /**
   * Get the Dynamic layout general settings.
   */
  public function getSettings();

  /**
   * Set the frontend library.
   */
  public function getFrontendLibrary();

  /**
   * Set the frontend library.
   *
   * @param string $frontend_library
   *   The frontend library we need to set.
   */
  public function setFrontendLibrary($frontend_library);

  /**
   * Get the first column number for a specific frontend library.
   *
   * @param string $frontend_library
   *   The frontend library we need to get the first column class from.
   * @param string $new_column_prefix
   *   The new column prefix.
   * @param string $new_grid_column_count
   *   The new grid column count.
   */
  public function getLastColumnNumber($frontend_library, $new_column_prefix, $new_grid_column_count);

  /**
   * Get the frontend column classes.
   */
  public function getFrontendColumnClasses();

  /**
   * Get the grid column count.
   */
  public function getGridColumnCount();

  /**
   * Get the column prefix.
   */
  public function getColumnPrefix();

  /**
   * Purge all column width numbers from all rows.
   *
   * This is needed if the frontend library is changed.
   *
   * @param string $last_column_number
   *   The last column number we need to set when purging the old classes.
   * @param string $new_column_prefix
   *   The new column prefix.
   */
  public function purgeColumnWidthNumbers($last_column_number, $new_column_prefix = '');

  /**
   * Set the column prefix.
   *
   * @param string $column_prefix
   *   The column prefix.
   */
  public function setColumnPrefix($column_prefix);

  /**
   * Set the grid column count.
   *
   * @param string $new_grid_column_count
   *   The grid column count.
   */
  public function setGridColumnCount($new_grid_column_count);

}
