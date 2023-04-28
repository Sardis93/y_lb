<?php

namespace Drupal\y_lb\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_builder\Form\OverridesEntityForm;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Provides a custom form containing the Layout Builder UI for overrides with WS Styles plugin management.
 */
class YLBOverridesEntityForm extends OverridesEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL) {
    $node = $this->entity;
    $settings = unserialize($node->styles->value) ?: [];

    $view_display = $this->entityTypeManager
      ->getStorage('entity_view_display')
      ->load('node.' . $node->bundle() . '.default');
    $global_settings = $view_display->getThirdPartySettings('y_lb');
    $default_settings = $global_settings['styles'] ?: [];


    $form['override_styles'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Override default WS Styles'),
      '#description' => $this->t('Whether or not the node has overridden default WS styles (e.g. Color scheme, etc.).'),
      '#default_value' => $node->override_styles->value,
    ];

    $form['ws_design_settings'] = [
      '#type' => 'ws_style_select',
      '#default_value' => array_merge($default_settings, $settings),
      '#states' => [
        'visible' => [
          ':input[name="override_styles"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    return parent::buildForm($form, $form_state, $section_storage);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($form_state->getValue('override_styles')) {
      $settings = $form_state->getValue('ws_design_settings');
      foreach ($settings as $group => $option) {
        if (empty($option)) {
          $element = $form['ws_design_settings'][$group];
          $form_state->setError($form['ws_design_settings'][$group], $this->t('Please choose value for %setting style', ['%setting' => $element['#title']]));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $styles = $form_state->getValue('override_styles') ? $form_state->getValue('ws_design_settings') : [];
    $this->entity->set('override_styles', $form_state->getValue('override_styles'));
    $this->entity->set('styles', serialize($styles));

    return parent::save($form, $form_state);
  }

}
