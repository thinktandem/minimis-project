<?php

namespace Drupal\dynamic_layouts\Form;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dynamic_layouts\DynamicLayoutSettingsInterface;
use Drupal\dynamic_layouts\DynamicLayoutConstants as Constants;

/**
 * Provides a generic settings form for the DynamicLayouts.
 */
class SettingsForm extends FormBase {

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
    return 'dynamic_layouts_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\dynamic_layouts\Entity\DynamicLayoutSettings $settings */
    $frontend_library_default = '';
    $column_prefix = '';
    $grid_column_count = '';
    if ($settings = $this->entityTypeManager->getStorage('dynamic_layout_settings')->load('settings')) {
      $frontend_library_default = $settings->getFrontendLibrary();
      $column_prefix = $settings->getColumnPrefix();
      $grid_column_count = $settings->getGridColumnCount();
    }

    if ($frontend_library_default) {
      drupal_set_message($this->t('When changing the settings, all configured "Column width classes" will be purged.'), 'warning');
    }

    $form[Constants::FRONTEND_LIBRARY] = array(
      '#type' => 'select',
      '#title' => t('Frontend library'),
      '#description' => t('Choose which frontend library you want to use for your layout.'),
      '#required' => TRUE,
      '#options' => [
        Constants::BOOTSTRAP => 'Bootstrap (v4)',
        Constants::CUSTOM => 'Custom..',
      ],
      '#default_value' => $frontend_library_default,
    );

    $form['column_prefix'] = array(
      '#type' => 'textfield',
      '#title' => t('Column prefix'),
      '#description' => t('Fill in you column prefix. As an example; Bootstrap uses "col" for prefix. A dash (-) will be added as a suffix.'),
      '#default_value' => $column_prefix,
      '#states' => [
        'visible' => [
          'select[name="frontend_library"]' => ['value' => Constants::CUSTOM],
        ],
        'required' => [
          'select[name="frontend_library"]' => ['value' => Constants::CUSTOM],
        ],
      ],
    );

    $form['grid_column_count'] = array(
      '#type' => 'select',
      '#title' => t('Grid column count'),
      '#description' => t('What is your column count in your grid?'),
      '#options' => [
        '6' => '6 columns grid',
        '8' => '8 columns grid',
        '12' => '12 columns grid',
      ],
      '#default_value' => $grid_column_count,
      '#states' => [
        'visible' => [
          'select[name="frontend_library"]' => ['value' => Constants::CUSTOM],
        ],
        'required' => [
          'select[name="frontend_library"]' => ['value' => Constants::CUSTOM],
        ],
      ],
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings */
    if (!$settings = $this->entityTypeManager->getStorage('dynamic_layout_settings')->load('settings')) {
      return NULL;
    }

    if ($form_state->getValue(Constants::FRONTEND_LIBRARY) == Constants::BOOTSTRAP) {

      $url = Url::fromUri('https://www.drupal.org/project/bootstrap_library');
      $link = Link::fromTextAndUrl(t('bootstrap_library'), $url)->toString();

      // Display a message.
      drupal_set_message(t('You have selected Bootstrap, to display the layout properly in the frontend: install the @link module & select version 4.x or implement Bootstrap (v4) in your theme.', ['@link' => $link]), 'warning');
    }

    $old_frontend_library = $settings->getFrontendLibrary();

    // Display a message to create a layout,
    // only when we had no frontend library.
    if (!$old_frontend_library) {
      $new_layout_link = Link::fromTextAndUrl(t('click here'), Url::fromRoute('dynamic_layout.dynamic_layout_add'))->toString();

      // Display a message.
      drupal_set_message(t('Settings have been saved, @link to add a Dynamic Layout!', array('@link' => $new_layout_link)));
    }

    $new_column_prefix = $form_state->getValue('column_prefix');
    $new_grid_column_count = $form_state->getValue('grid_column_count');

    // Set the form values.
    if ($new_frontend_library = $form_state->getValue(Constants::FRONTEND_LIBRARY)) {

      // Get the last column number, we set this after purging the old ones.
      $last_column_number = $settings->getLastColumnNumber($new_frontend_library, $new_column_prefix, $new_grid_column_count);

      if ($new_frontend_library == Constants::BOOTSTRAP) {
        $new_column_prefix = 'col';
      }

      $this->updateValues($new_column_prefix,
        $new_grid_column_count,
        $last_column_number,
        $old_frontend_library,
        $new_frontend_library,
        $settings
      );
    }

    $settings->save();
  }

  /**
   * Update the setting values.
   *
   * @param string $new_column_prefix
   *   The new column prefix.
   * @param int $new_grid_column_count
   *   The new grid column count.
   * @param int $last_column_number
   *   The last column number.
   * @param object $old_frontend_library
   *   The old frontend library.
   * @param object $new_frontend_library
   *   The new frontend library.
   * @param \Drupal\dynamic_layouts\DynamicLayoutSettingsInterface $settings
   *   The settings object.
   */
  public function updateValues(
    $new_column_prefix,
    $new_grid_column_count,
    $last_column_number,
    $old_frontend_library,
    $new_frontend_library,
    DynamicLayoutSettingsInterface $settings) {

    // Column prefix changed?
    if ($new_column_prefix) {
      $old_column_prefix = $settings->getColumnPrefix();
      if ($old_column_prefix != $new_column_prefix) {
        $settings->purgeColumnWidthNumbers($last_column_number, $new_column_prefix);
      }
      $settings->setColumnPrefix($new_column_prefix);
    }

    // Grid column count changed?
    if ($new_grid_column_count) {
      $old_grid_column_count = $settings->getGridColumnCount();
      if ($old_grid_column_count != $new_grid_column_count) {
        $settings->purgeColumnWidthNumbers($last_column_number);
      }
      $settings->setGridColumnCount($new_grid_column_count);
    }

    // Frontend library changed?
    if ($old_frontend_library != $new_frontend_library) {
      $settings->purgeColumnWidthNumbers($last_column_number, $new_column_prefix);
      if ($old_frontend_library) {
        drupal_set_message($this->t('All column widths have been purged, please reconfigure your layouts!'), 'error');
      }
      $settings->setFrontendLibrary($new_frontend_library);
    }
  }

}
