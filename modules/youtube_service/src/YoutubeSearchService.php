<?php

namespace Drupal\youtube_service;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use GuzzleHttp\Client;

/**
 *
 */
class YoutubeSearchService {

  /**
   * $client = \Drupal::httpClient();.
   */
  protected $client;

  /**
   *
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   *
   */
  public function search($search_term, $no) {

    $keywords = (explode(" ", $search_term));

    $newString = implode("+", $keywords);

    $q = '&q=' . $newString;

    $maxResults = '&maxResults=' . $no;

    $part = 'part=snippet';

    $key = '&key=AIzaSyChwseCpjkwEjFer9wNifZOLacU7xLCsEM';

    // $client = \Drupal::httpClient();
    $baseurl = 'https://www.googleapis.com/youtube/v3/search?';


    $request = $this->client->request('GET', $baseurl . $part . $maxResults . $q . $key);
    // kint($this->client);
    // exit();
    $response = json_decode($request->getBody());

    $i = 0;

    foreach ($response->items as $key => $value) {
      if ($i < 1) {
        $videoid = $value->id->videoId;
        $title = $value->snippet->title;
        $description = $value->snippet->description;
        $this->addNode($title, $description, $videoid);
      }
    }
  }

  /**
   *
   */
  public function addTerm($search_term, $id) {
    $term = Term::create([
      'name' => $search_term,
      'vid'  => $id,
    ])->save();

  }

  /**
   *
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
