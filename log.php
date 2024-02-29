<?php

$fields = [
	'accelerometerX',
	'accelerometerY',
	'accelerometerZ',
	'gyroX',
	'gyroY',
	'gyroZ',
	'temperature',
	'Kp',
	'Ki',
	'Kd',
	'error'
];

$filePath = 'data.json';

// Check if it's a POST request and save data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$inputData = json_decode(file_get_contents('php://input'), true);
	$response = [];
	foreach ($fields as $field) {
		$response[$field] = array_key_exists($field, $inputData) ? $inputData[$field] : 'No data';
	}

	// Add a timestamp
	$response['timestamp'] = time();

	// Read existing data
	$existingData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
	if (!is_array($existingData)) {
		$existingData = [];
	}

	// Append new data
	$existingData[] = $response;

	// Save updated data to JSON file
	file_put_contents($filePath, json_encode($existingData));
}

// If it's a GET request, read data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	header('Content-Type: application/json');

	// Check if file exists and return its content
	if (file_exists($filePath)) {
		$contents = file_get_contents($filePath);
		$data = json_decode($contents, true);

		// Calculate time since last update
		$lastUpdate = $data ? $data[count($data) - 1]['timestamp'] : 0;
		$timeSinceLastUpdate = time() - $lastUpdate;

		if ($timeSinceLastUpdate > 5) {
			echo json_encode([
				'error' => 'No recent data available',
				'timeSinceLastUpdate' => $timeSinceLastUpdate,
				'dataCount' => count($data)
			]);
		} else {
			echo json_encode([
				'data' => $data[count($data) - 1],
				'timeSinceLastUpdate' => $timeSinceLastUpdate,
				'dataCount' => count($data)
			]);
		}
	} else {
		echo json_encode(['error' => 'No data available']);
	}
}
