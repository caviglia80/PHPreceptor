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
$apiKey = 'tu_clave_api_secreta'; // Define tu clave API aquí

// Manejo de errores básico
function errorResponse($message, $code = 400)
{
	http_response_code($code);
	echo json_encode(['error' => $message]);
	exit;
}

// Autenticación para solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

	if ($providedKey !== $apiKey) {
		errorResponse('Acceso denegado', 403);
	}

	$inputData = json_decode(file_get_contents('php://input'), true);
	if (!is_array($inputData)) {
		errorResponse('Entrada inválida');
	}

	$response = [];
	foreach ($fields as $field) {
		// Valida y sanitiza
		$response[$field] = array_key_exists($field, $inputData) ? htmlspecialchars($inputData[$field]) : 'No data';
	}

	// Añade un timestamp
	$response['timestamp'] = time();

	// Lee los datos existentes
	$existingData = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
	if (!is_array($existingData)) {
		$existingData = [];
	}

	// Agrega los nuevos datos
	$existingData[] = $response;

	// Guarda los datos actualizados en el archivo JSON
	if (file_put_contents($filePath, json_encode($existingData)) === false) {
		errorResponse('Error al guardar los datos', 500);
	}

	echo json_encode(['message' => 'Datos guardados correctamente']);
	exit;
}

// Para solicitudes GET, lee los datos
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	header('Content-Type: application/json');

	if (file_exists($filePath)) {
		$contents = file_get_contents($filePath);
		$data = json_decode($contents, true);

		$lastUpdate = $data ? $data[count($data) - 1]['timestamp'] : 0;
		$timeSinceLastUpdate = time() - $lastUpdate;

		echo json_encode([
			'data' => $data[count($data) - 1] ?? null,
			'timeSinceLastUpdate' => $timeSinceLastUpdate,
			'dataCount' => count($data)
		]);
	} else {
		echo json_encode(['error' => 'No data available']);
	}
	exit;
}
