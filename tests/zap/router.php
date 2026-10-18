<?php

$publicDirectory = realpath(__DIR__ . '/../../public');
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedFile = realpath($publicDirectory . DIRECTORY_SEPARATOR . ltrim(rawurldecode($requestPath), '/\\'));

if (
    $requestedFile !== false &&
    strcasecmp(substr($requestedFile, 0, strlen($publicDirectory) + 1), $publicDirectory . DIRECTORY_SEPARATOR) === 0 &&
    is_file($requestedFile) &&
    basename($requestedFile)[0] !== '.'
) {
    $contentType = mime_content_type($requestedFile);
    if ($contentType === false) {
        http_response_code(500);
        exit('Could not determine the static file content type.');
    }

    header_remove('X-Powered-By');
    header('Content-Type: ' . $contentType);
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');

    readfile($requestedFile);
    exit;
}

require $publicDirectory . '/index.php';
