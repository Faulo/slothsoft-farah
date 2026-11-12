<?php
declare(strict_types = 1);

$server = stream_socket_server('tcp://127.0.0.1:0', $error, $message);
if ($server === false) {
    throw new RuntimeException("Failed to start test server: $message", $error ?? 0);
}
try {
    $address = stream_socket_get_name($server, false);
    if ($address === false) {
        throw new RuntimeException('Failed to obtain test server address.');
    }
    echo substr($address, strrpos($address, ':') + 1) . PHP_EOL;
    flush();
    $client = stream_socket_accept($server, 5);
    if ($client === false) {
        throw new RuntimeException('Test server did not receive a connection.');
    }
    try {
        stream_set_timeout($client, 5);
        $payload = '';
        do {
            $chunk = fread($client, 65535);
            if ($chunk === false || $chunk === '') {
                throw new RuntimeException('Test server did not receive complete JSON.');
            }
            $payload .= $chunk;
            $decoded = json_decode($payload, true);
        } while (json_last_error() !== JSON_ERROR_NONE);
        $response = ($argv[1] ?? '') === 'length'
            ? json_encode(is_string($decoded) ? strlen($decoded) : 0, JSON_THROW_ON_ERROR)
            : $payload;
        if (fwrite($client, $response) !== strlen($response)) {
            throw new RuntimeException('Test server failed to send its response.');
        }
    } finally {
        fclose($client);
    }
} finally {
    fclose($server);
}
