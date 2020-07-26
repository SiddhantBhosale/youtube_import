<?php

namespace Drupal\youtube_service\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\youtube_service\YoutubeSearchService;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\CssCommand;

/**
 * YoutubeSearchForm class which contains the form for searching youtube videos.
 */
class YoutubeSearchForm extends FormBase {
  protected $customService;

  /**
   * Constructor to instantiate the YoutubeSearchService.
   */
  public function __construct(YoutubeSearchService $customService) {
    $this->customService = $customService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('youtube.default')
    );
  }

  /**
   * Function to get the FormId.
   */
  public function getFormId() {
    return 'youtube_search';
  }

  /**
   * Build the form to search the youtube videos.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['title'] = [
      '#type' => 'markup',
      '#markup' => 'Search vidoes from Youtube',
    ];

    $form['video_search'] = [
      '#type' => 'textfield',
      '#title' => 'Keyword',
      '#required' => TRUE,
      '#prefix' => '<div id="search-result"></div>',
      '#ajax' => [
        'callback' => '::checkSearchValidation',
        'effect' => 'fade',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];

    $form['results_no'] = [
      '#type' => 'number',
      '#title' => 'No of Results',
      '#default_value' => 20,
    ];

    $form['search'] = [
      '#type' => 'submit',
      '#value' => 'Search',
    ];

    return $form;
  }

  /**
   * Validate function.
   */
  public function checkSearchValidation(array $form, FormStateInterface $form_state) {

    $ajax_response = new AjaxResponse();

    if (!($this->validateSearch($form, $form_state))) {
      $css = ['border' => '1.5px solid red'];
      $text = 'Searched keyword already exits';
      $ajax_response->addCommand(new CssCommand('#edit-video-search', $css));
    }
    else {
      $text = ' ';
      $css = ['border' => '1px solid #ccc'];
      $ajax_response->addCommand(new CssCommand('#edit-video-search', $css));
    }
    $ajax_response->addCommand(new HtmlCommand('#search-result', $text));
    return $ajax_response;
  }

  /**
   * Validate if the search term is already present.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!($this->validateSearch($form, $form_state))) {
      $form_state->setErrorByName('video_search', $this->t('This keyword already exists, try new keywords.'));
    }
  }

  /**
   * Helper function the searched keyword if they already exist or not.
   */
  public function validateSearch(array &$form, FormStateInterface $form_state) {

    $properties = [];
    $name = $form_state->getValue('video_search');
    if (!empty($name)) {
      $properties['name'] = $name;
    }
    $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load('search_list');
    $vid = $vocabulary->get('vid');
    if (!empty($vid)) {
      $properties['vid'] = $vid;
    }
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties($properties);
    $term = reset($terms);
    if (empty($term)) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

  /**
   * Send the search term to Youtube API.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $search_term = $form_state->getValue('video_search');
    $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load('search_list');
    $id = $vocabulary->get('vid');
    $this->customService->addTerm($search_term, $id);
    $num = $form_state->getValue('results_no');
    $this->customService->search($search_term, $num);
    drupal_set_message(t('Successfull.....!!!!! Adding the videos.'));
  }

}
