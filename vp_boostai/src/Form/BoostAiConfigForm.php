<?php

namespace Drupal\vp_boostai\Form;

use Drupal\Core\Datetime\DateHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure boost ai settings.
 */
class BoostAiConfigForm extends ConfigFormBase {

  /**
   * Constant for visibility Boost AI.
   */
  const BOOSTAI_VISIBILITY_NOTLISTED = 1;

  /**
   * Constant for visibility Boost AI.
   */
  const BOOSTAI_VISIBILITY_LISTED = 2;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'vp_boostai.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vp_entry_ban_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $visibility_options = [
      self::BOOSTAI_VISIBILITY_NOTLISTED => $this->t('All pages except those listed'),
      self::BOOSTAI_VISIBILITY_LISTED => $this->t('Only the listed pages'),
    ];
    
    $form['vp_boostai_js_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chat panel JavaScript'),
      '#description' => $this->t('URL to chat panel JavaScript (e.g. https://buerokratt.YOURDOMAIN.ee:3000/widget_bundle.js)'),
      '#required' => TRUE,
      '#default_value' => $config->get('vp_boostai_js_url'),
    ];

    $form['vp_boostai_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base API URL'),
      '#description' => $this->t('Bürokratt API endpoint URL (e.g. https://ruuter.buerokratt.YOURDOMAIN.ee:8080)'),
      '#required' => TRUE,
      '#default_value' => $config->get('vp_boostai_api_url'),
    ];

    $form['vp_boostai_authentication_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authentication URL'),
      '#required' => TRUE,
      '#description' => $this->t('Authentication endpoint URL (e.g. https://tim.buerokratt.YOURDOMAIN.ee:8085)'),
      '#default_value' => $config->get('vp_boostai_authentication_url'),
    ];

    $form['vp_boostai_office_wrapper'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#title' => $this->t('Office data'),
      '#description' => $this->t('Information about office schedule.'),
    ];

    $form['vp_boostai_office_wrapper']['office_start_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Office start time'),
      '#min' => 0,
      '#max' => 24,
      '#default_value' => $config->get('office_start_time'),
    ];

    $form['vp_boostai_office_wrapper']['office_end_time'] = [
      '#type' => 'number',
      '#title' => $this->t('Office end time'),
      '#min' => 0,
      '#max' => 24,
      '#default_value' => $config->get('office_end_time'),
    ];

    $form['vp_boostai_office_wrapper']['office_days'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Office days'),
      '#options' => [
        '1' => $this->t('Monday'),
        '2' => $this->t('Tuesday'),
        '3' => $this->t('Wednesday'),
        '4' => $this->t('Thursday'),
        '5' => $this->t('Friday'),
        '6' => $this->t('Saturday'),
        '7' => $this->t('Sunday'),
      ],
      '#default_value' => $config->get('office_days'),
    ];

    $form['vp_boostai_show_open'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show chatbox open'),
      '#required' => FALSE,
      '#default_value' => $config->get('vp_boostai_show_open'),
    ];

    $form['vp_boostai_show_open_mobile'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show chatbox open (mobile)'),
      '#required' => FALSE,
      '#default_value' => $config->get('vp_boostai_show_open_mobile'),
    ];

    $form['vp_boostai_input_focus_color'] = [
      '#type' => 'color',
      '#title' => $this->t('Input focus color'),
      '#required' => FALSE,
      '#default_value' => $config->get('vp_boostai_input_focus_color'),
    ];

    $form['vp_boostai_visibility'] = [
      '#type' => 'radios',
      '#title' => $this->t('Show chat on specific pages'),
      '#required' => TRUE,
      '#default_value' => $config->get('vp_boostai_visibility'),
      '#options' => $visibility_options,
    ];

    $form['vp_boostai_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#required' => FALSE,
      '#default_value' => $config->get('vp_boostai_pages'),
      '#description' => $this->t('/node/231'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('vp_boostai_api_url', $form_state->getValue('vp_boostai_api_url'))
      ->set('vp_boostai_authentication_url', $form_state->getValue('vp_boostai_authentication_url'))
      ->set('vp_boostai_js_url', $form_state->getValue('vp_boostai_js_url'))
      ->set('vp_boostai_show_open', $form_state->getValue('vp_boostai_show_open'))
      ->set('vp_boostai_show_open_mobile', $form_state->getValue('vp_boostai_show_open_mobile'))
      ->set('vp_boostai_input_focus_color', $form_state->getValue('vp_boostai_input_focus_color'))
      ->set('vp_boostai_visibility', $form_state->getValue('vp_boostai_visibility'))
      ->set('vp_boostai_pages', $form_state->getValue('vp_boostai_pages'))
      ->set('office_start_time', $form_state->getValue('office_start_time'))
      ->set('office_end_time', $form_state->getValue('office_end_time'))
      ->set('office_days', $form_state->getValue('office_days'))
      ->save();

    // Flush asset file caches.
    \Drupal::service('asset.css.collection_optimizer')->deleteAll();
    \Drupal::service('asset.js.collection_optimizer')->deleteAll();
    _drupal_flush_css_js();

    parent::submitForm($form, $form_state);
  }
}
