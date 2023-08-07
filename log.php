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

// Check if it's a POST request and save data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$response = array();
	foreach ($fields as $field) {
		$response[$field] = isset($_POST[$field]) ? $_POST[$field] : 'No data';
	}

    // Add a timestamp
	$response['timestamp'] = time();

    // Save data to a JSON file
	file_put_contents('data.json', json_encode($response));
}

// If it's a GET request, read data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	header('Content-Type: application/json');

    // Check if file exists and return its content
	if (file_exists('data.json')) {
		$contents = file_get_contents('data.json');
		$data = json_decode($contents, true);

        // Check if more than 4 seconds have passed
		if (time() - $data['timestamp'] > 5) {
			$response = array();
			foreach ($fields as $field) {
				$response[$field] = 'No data';
			}

            // Add a timestamp
			$response['timestamp'] = time();

			echo json_encode($response);
		} else {
			echo $contents;
		}
	} else {
		echo json_encode(['error' => 'No data available']);
	}
}
?>
