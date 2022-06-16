<?php

// Call Elasticsearch helper (installed via Composer)
require 'vendor/autoload.php';

// Define Elasticsearch hosts; typically 'localhost' for all-in-one Scrapsey installation
$hosts = ["http://localhost:9200"];

// Build Elasticsearch connection
$client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();

?>