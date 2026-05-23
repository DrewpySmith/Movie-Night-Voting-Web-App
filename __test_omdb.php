<?php
$key = '998de2c6';
$url = 'https://www.omdbapi.com/?apikey=' . $key . '&s=spider&type=movie';
$response = file_get_contents($url);
if ($response === false) { echo "HTTP request failed\n"; exit(1); }
$data = json_decode($response, true);
echo "Response: " . ($data['Response'] ?? 'N/A') . "\n";
if (isset($data['Error'])) echo "Error: " . $data['Error'] . "\n";
echo "Total results: " . ($data['totalResults'] ?? 0) . "\n";
echo "First result: " . json_encode($data['Search'][0] ?? null, JSON_PRETTY_PRINT) . "\n";
