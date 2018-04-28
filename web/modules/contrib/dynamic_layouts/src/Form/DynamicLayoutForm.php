<?php

namespace Drupal\dynamic_layouts\Form;

use Drupal\Core\Url;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dynamic_layouts\DynamicLayoutInterface;
use Drupal\dynamic_layouts\DynamicLayoutConstants as Constants;

/**
 * Form controller for the DynamicLayout entity edit forms.
 */
class DynamicLayoutForm extends EntityForm {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Constructs a new DynamicLayoutForm.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\dynamic_layouts\DynamicLayoutInterface $entity */
    $entity = $this->entity;

    // Attach dynamic layout & popups dialogs/modals libraries.
    $form['#attached']['library'][] = 'dynamic_layouts/dynamic_layouts';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    // Disable caching for the form.
    $form['#cache'] = ['max-age' => 0];

    // Do not flatten nested form fields.
    $form['#tree'] = TRUE;

    // Check if the entity is new, set some vars.
    $entity_is_new = FALSE;
    if ($entity->isNew()) {
      $entity_is_new = TRUE;
    }

    $this->addSettingsFormFields($form, $entity);

    $form[Constants::DYNAMIC_LAYOUT] = array(
      '#type' => 'vertical_tabs',
      '#title' => t('Dynamic layout'),
    );

    if (!$entity_is_new) {

      // We need to set the action to current entity url because form action
      // is changing when using: new ReplaceCommand in all Ajax forms.
      $url = $entity->toUrl()->toString();
      $form['#action'] = $url;

      $form[Constants::LAYOUT] = [
        '#type' => 'container',
        '#title' => $this->t('Layout'),
        '#tree' => TRUE,
        // Set up the wrapper so that AJAX will be able to replace the fieldset.
        '#prefix' => '<div id="js-ajax-elements-wrapper">',
        '#suffix' => '</div>',
      ];
      $form[Constants::LAYOUT][Constants::LAYOUT_GROUP] = [
        '#type' => 'details',
        '#title' => $this->t('Layout'),
        '#tree' => TRUE,
        '#group' => Constants::DYNAMIC_LAYOUT,
        '#description' => t('To change the default names of the columns, click "Edit column".'),
        '#attributes' => [
          'class' => ['layout-group'],
        ],
      ];

      $addRowTitle = t('Add row');

      $form[Constants::LAYOUT][Constants::LAYOUT_GROUP]['add_row_top'] = [
        '#type' => 'link',
        '#name' => 'add_row',
        '#title' => $addRowTitle,
        '#prefix' => '<div class="add-row-wrapper">',
        '#suffix' => '</div>',
        '#url' => Url::fromRoute(
          'dynamic_layouts.add_row',
          [
            'dynamic_layout_id' => $entity->id(),
          ]
        ),
        '#attributes' => [
          'class' => [
            'btn',
            'add-row',
            'use-ajax',
          ],
          'title' => $addRowTitle,
        ],
      ];
    }

    // Add the row fields.
    $this->addRowFormFields($form);

    $form_state->setCached(FALSE);

    // Submit.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Testing'),
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function addSettingsFormFields(array &$form, DynamicLayoutInterface $entity) {

    // Check if the entity is new, set some vars.
    $layout_tab_weight = 1;
    $entity_is_new = FALSE;
    if ($entity->isNew()) {
      $entity_is_new = TRUE;
      $layout_tab_weight = 0;
    }

    $form[Constants::GENERAL_SETTING] = array(
      '#type' => 'details',
      '#title' => t('General settings'),
      '#group' => Constants::DYNAMIC_LAYOUT,
      '#weight' => $layout_tab_weight,
    );

    $form[Constants::GENERAL_SETTING]['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Layout name'),
      '#default_value' => $entity->label(),
      '#size' => 30,
      '#required' => TRUE,
      '#maxlength' => 64,
      '#description' => $this->t('The name for this layout.'),
    ];
    $form[Constants::GENERAL_SETTING]['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#required' => TRUE,
      '#disabled' => !$entity->isNew(),
      '#size' => 30,
      '#maxlength' => 64,
      '#machine_name' => [
        'exists' => ['\Drupal\dynamic_layouts\Entity\DynamicLayout', 'load'],
        'source' => [Constants::GENERAL_SETTING, 'label'],
      ],
    ];
    $form[Constants::GENERAL_SETTING][Constants::CATEGORY] = [
      '#type' => 'textfield',
      '#title' => $this->t('Layout category'),
      '#default_value' => $entity->getCategory(),
      '#size' => 30,
      '#required' => TRUE,
      '#maxlength' => 64,
      '#description' => $this->t('The category of this layout.'),
    ];

    $form[Constants::GENERAL_SETTING][Constants::DEFAULT_ROW_CLASS] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default row class'),
      '#default_value' => $entity->getDefaultRowClass(),
      '#size' => 30,
      '#maxlength' => 64,
      '#required' => TRUE,
      '#description' => $this->t('This class will be used on every row.'),
    ];

    $form[Constants::GENERAL_SETTING][Constants::DEFAULT_COLUMN_CLASS] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default column class'),
      '#default_value' => $entity->getDefaultColumnClass(),
      '#size' => 30,
      '#maxlength' => 64,
      '#description' => $this->t('This class will be used on every column.'),
    ];

    if ($entity_is_new) {
      $form[Constants::GENERAL_SETTING][Constants::START_ROWS_COUNT] = [
        '#type' => 'number',
        '#title' => $this->t('Layout rows'),
        '#description' => $this->t('After saving you can add, remove configure the rows'),
        '#min' => 1,
        '#max' => 50,
        '#default_value' => 1,
        '#size' => 30,
        '#required' => TRUE,
        '#maxlength' => 64,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function addRowFormFields(array &$form) {

    /** @var \Drupal\dynamic_layouts\Entity\DynamicLayout $entity */
    $entity = $this->entity;

    // Check if we need to render rows.
    if ($rows = $entity->getRows()) {

      $wrapper_classes = $this->getWrapperClasses();

      $form[Constants::LAYOUT][Constants::LAYOUT_GROUP]['rows']['wrapper'] = [
        '#type' => 'markup',
        '#prefix' => '<div class="' . $wrapper_classes . '">',
        '#suffix' => '</div>',
      ];

      // Loop over the rows.
      $row_count = 1;
      foreach ($rows as $row) {
        if (!isset($row['row_id'])) {
          continue;
        }

        $row_id = $row['row_id'];
        $row['row_count'] = $row_count;

        $addLinkOptions = $editLinkOptions = $deleteLinkOptions = [
          'attributes' => [
            'class' => ['use-ajax', 'btn'],
          ],
        ];

        $addLinkTitle = t('Add column');
        $editLinkTitle = t('Edit row');
        $deleteLinkTitle = t('Delete row');

        $addLinkOptions['attributes']['class'][] = 'add-column-link';
        $editLinkOptions['attributes']['class'][] = 'edit-link';
        $deleteLinkOptions['attributes']['class'][] = 'delete-link';

        $addLinkOptions['attributes']['title'] = $addLinkTitle;
        $editLinkOptions['attributes']['title'] = $editLinkTitle;
        $deleteLinkOptions['attributes']['title'] = $deleteLinkTitle;

        $row['add_column_link'] = $entity->getRowLink($row_id, 'dynamic_layouts.add_column', $addLinkTitle, $addLinkOptions);
        $row['edit_row_link'] = $entity->getRowLink($row_id, 'dynamic_layouts.edit_row_modal_form', $editLinkTitle, $editLinkOptions);
        $row['delete_row_link'] = $entity->getRowLink($row_id, 'dynamic_layouts.delete_row', $deleteLinkTitle, $deleteLinkOptions);

        $elements = [
          '#theme' => 'dynamic_layouts_backend',
          '#row' => $row,
        ];
        $rendered_row = $this->renderer->render($elements);

        $form[Constants::LAYOUT][Constants::LAYOUT_GROUP]['rows']['wrapper'][$row_id] = [
          '#type' => 'inline_template',
          '#template' => '{{ row | raw }}',
          '#context' => [
            'row' => $rendered_row,
          ],
        ];

        $form[Constants::LAYOUT][Constants::LAYOUT_GROUP]['rows']['wrapper'][$row_id]['actions'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => [
              'actions-wrapper',
              'col-12',
            ],
          ],
        ];

        $row_count++;
      }

      $addRowTitle = t('Add row');

      $form[Constants::LAYOUT][Constants::LAYOUT_GROUP]['add_row_bottom'] = [
        '#type' => 'link',
        '#name' => 'add_row',
        '#title' => $addRowTitle,
        '#prefix' => '<div class="add-row-wrapper">',
        '#suffix' => '</div>',
        '#url' => Url::fromRoute(
          'dynamic_layouts.add_row',
          [
            'dynamic_layout_id' => $entity->id(),
          ]
        ),
        '#attributes' => [
          'class' => [
            'btn',
            'add-row',
            'use-ajax',
          ],
          'title' => $addRowTitle,
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\dynamic_layouts\Entity\DynamicLayout $entity */
    $entity = $this->entity;

    // Change submit button title if entity is new.
    $submit_title = $this->t('Save layout');
    if ($entity->isNew()) {
      $submit_title = $this->t('Save and configure rows');
    }

    // Set the custom submit title.
    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $submit_title,
      '#submit' => ['::submitForm', '::save'],
    ];

    if (!$this->entity->isNew() && $this->entity->hasLinkTemplate('delete-form')) {
      $route_info = $this->entity->toUrl('delete-form');
      if ($this->getRequest()->query->has('destination')) {
        $query = $route_info->getOption('query');
        $query['destination'] = $this->getRequest()->query->get('destination');
        $route_info->setOption('query', $query);
      }
      $actions['delete'] = [
        '#type' => 'link',
        '#title' => $this->t('Delete'),
        '#access' => $this->entity->access('delete'),
        '#attributes' => [
          'class' => ['button', 'button--danger'],
        ],
      ];
      $actions['delete']['#url'] = $route_info;
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function getWrapperClasses() {
    $wrapper_classes_arr = ['container-fluid'];

    /** @var \Drupal\dynamic_layouts\Entity\DynamicLayoutSettings $settings */
    $frontend_library_class = 'custom';
    if ($settings = $this->entityTypeManager->getStorage('dynamic_layout_settings')->load('settings')) {
      $frontend_library = $settings->getFrontendLibrary();
      if ($frontend_library == 'bootstrap') {
        $wrapper_classes_arr[] = $frontend_library_class . '-12';
      }
      else {
        if ($column_count = $settings->getGridColumnCount()) {
          $wrapper_classes_arr[] = $frontend_library . '-' . $column_count;
        }
      }
    }

    $wrapper_classes_arr[] = $frontend_library_class;

    return implode(' ', $wrapper_classes_arr);
  }

  /**
   * {@inheritdoc}
   */
  public function setFormValues($general_settings, DynamicLayoutInterface $entity, $entity_is_new) {
    // Set layout values in entity.
    if (isset($general_settings['label']) && $general_settings['label']) {
      $entity->set('label', trim($general_settings['label']));
    }
    if (isset($general_settings['id']) && $general_settings['id']) {
      $entity->set('id', trim($general_settings['id']));
    }
    if (isset($general_settings[Constants::CATEGORY]) && $general_settings[Constants::CATEGORY]) {
      $entity->set(Constants::CATEGORY, trim($general_settings[Constants::CATEGORY]));
    }

    // When a new entity is made, these classes are set in addStartingRows().
    // So only set these here if entity is not new.
    if (!$entity_is_new) {
      if (isset($general_settings[Constants::DEFAULT_COLUMN_CLASS]) && $general_settings[Constants::DEFAULT_COLUMN_CLASS]) {
        $entity->setDefaultColumnClass(trim($general_settings[Constants::DEFAULT_COLUMN_CLASS]));
      }
      if (isset($general_settings[Constants::DEFAULT_ROW_CLASS]) && $general_settings[Constants::DEFAULT_ROW_CLASS]) {
        $entity->setDefaultRowClass(trim($general_settings[Constants::DEFAULT_ROW_CLASS]));
      }
    }

    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Check for the layout form element.
    if (!$general_settings = $form_state->getValue(Constants::GENERAL_SETTING)) {
      return NULL;
    }

    /** @var \Drupal\dynamic_layouts\DynamicLayoutInterface $entity */
    $entity = $this->entity;

    // Check if we need to redirect to the list.
    $entity_is_new = FALSE;
    if ($entity->isNew()) {
      $entity_is_new = TRUE;
    }

    $this->setFormValues($general_settings, $entity, $entity_is_new);

    // Save the entity, add the starting rows and set the action.
    $status = $entity->save();
    if ($entity_is_new && isset($general_settings[Constants::START_ROWS_COUNT]) && $general_settings[Constants::START_ROWS_COUNT]) {
      $entity->addStartingRows($general_settings);
    }
    $action = $status == SAVED_UPDATED ? 'updated' : 'added';

    // Tell the user we've updated their layout.
    drupal_set_message($this->t('Layout %label has been %action.', ['%label' => $entity->label(), '%action' => $action]));
    $this->logger(Constants::DYNAMIC_LAYOUT)->notice('Layout %label has been %action.', array('%label' => $entity->label(), '%action' => $action));

    // Clear all plugin caches.
    // This is needed to display the Dynamic Layouts in Display Suite.
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();

    // Redirect back to the list view if layout is not new.
    if (!$entity_is_new) {
      $form_state->setRedirect('dynamic_layout.dynamic_layout_list');
    }
    else {
      $form_state->setRedirect(
        'entity.dynamic_layout.edit_form',
        [Constants::DYNAMIC_LAYOUT => $entity->id()]
      );
    }
  }

}
