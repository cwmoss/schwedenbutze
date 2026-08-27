<?php

require_once '../vendor/autoload.php';

use Sanity\Client as SanityClient;

$reservations = new Reservations(
  $_SERVER,
  getenv("X_API_KEY")
);

$reservations->send_cors();
$reservations->check_referer();
$reservations->check_token();
$res = $reservations->upnext();

echo $res;

class Reservations {

  public string $origin;

  public function __construct(
      private array $server,
      private string $token,
  ) {
      $this->origin = $server['HTTP_ORIGIN'] ?? $server["HTTP_REFERER"];
  }

  public function upnext() {

    $client = new SanityClient([
      'projectId' => 'emjk7lsc',
      'dataset' => 'production',
      // Whether or not to use the API CDN for queries. Default is false.
      'useCdn' => true,
      // If you are starting a new project, using the current UTC date is usually
      // a good idea. See "Specifying API version" section for more details
      'apiVersion' => '2019-01-29',
    ]);
    
    $results = $client->fetch(
    
      '*[_type == $type && arrival >= $today || departure >= $today]', // Query
      ['type' => 'reservation', 'today' => $date = date('Y-m-d')] // Params (optional)
    );
    
    $bookings = [];
    
    foreach ($results as $reservation) {
      array_push($bookings, array($reservation['arrival'], $reservation['departure']));
    }
    
    return json_encode($bookings);    
  }

  function check_referer() {
    $headers = $this->server;

    // local (dev) installation?
    if ($headers['HTTP_HOST'] == 'localhost') {
        return true;
    }

    if ($headers['HTTP_HOST'] == 'schwedenbutze.de') {
      return true;
    }

    if (!isset($headers['SLFT_WEBDEPLOY_ALLOWED_HOSTS'])) {
        return true;
    }

    $allowed = ['localhost', 'schwedenbutze.de'];
    //$allowed = explode(" ", $headers['SLFT_WEBDEPLOY_ALLOWED_HOSTS']);

    # sometimes referer doesn't include the full url (/dashboard)
    # if(!preg_match("!/dashboard$!", $headers['HTTP_REFERER'])) return false;

    $you = $_SERVER["HTTP_ORIGIN"] ?? null;
    if (!$you) $you = $_SERVER["HTTP_REFERER"] ?? null;
    $remote = parse_url($you, PHP_URL_HOST);

    $ok = in_array($remote, $allowed);
    if (!$ok) throw new Exception("failed");
  }

  function check_token() {
    $hdrs = getallheaders();
    $hdrs = array_change_key_case($hdrs);
    $ok = isset($hdrs['x-api-key']) && $this->token && $hdrs['x-api-key'] && $hdrs['x-api-key'] === $this->token;
    if (!$ok) throw new Exception("auth failed");
  }

  public function send_cors() {
    // TODO check list;

    header('Access-Control-Allow-Origin: ' . $this->origin);
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    if (array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_HEADERS', $this->server)) {
        header('Access-Control-Allow-Headers: '
            . $this->server['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
    } else {
        //   header('Access-Control-Allow-Headers: *');
    }

    header('Access-Control-Allow-Credentials: true');
    #  header('Access-Control-Allow-Headers: Authorization');
    header('Access-Control-Expose-Headers: Authorization');

    if ("OPTIONS" == $this->server['REQUEST_METHOD']) {
        exit(0);
    }
  }
}

?>