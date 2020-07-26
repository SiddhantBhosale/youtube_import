<?php

namespace Drupal\youtube_service;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\Client;

/**
 * Class for Youtube Search Service.
 */
class YoutubeSearchService {

  /**
   * $client = \Drupal::httpClient();.
   */
  protected $client;

  /**
   * Constructs the YoutubeSearchService.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   * Function to search the videos on youtube.
   */
  public function search($search_term, $no) {
    $keywords = (explode(" ", $search_term));
    $keywords = implode("+", $keywords);
    $q = '&q=' . $keywords;
    $maxResults = '&maxResults=' . $no;
    $part = 'part=snippet';
    $key = '&key=your_key';
    $baseurl = 'https://www.googleapis.com/youtube/v3/search?';
    $request = $this->client->request('GET', $baseurl . $part . $maxResults . $q . $key);
    $response = json_decode($request->getBody());

    foreach ($response->items as $key => $value) {
      $videoid = $value->id->videoId;
      $title = $value->snippet->title;
      $description = $value->snippet->description;
      $this->addNode($title, $description, $videoid);
    }
  }

  /**
   * Function to add search keywords as terms to search_list vocabulary.
   */
  public function addTerm($search_term, $id) {
    $term = Term::create([
      'name' => $search_term,
      'vid'  => $id,
    ])->save();

  }

  /**
   * Function to create nodes of searched videos.
   */
  public function addNode($title, $description, $videoid) {
    $url = 'https://www.youtube.com/watch?v=' . $videoid;
    $node = Node::create([
      'type' => 'youtube_videos',
      'title' => $title,
      'body' => $description,
      'field_video' => $url,
    ]);
    $node->save();
  }

}
