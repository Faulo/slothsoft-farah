<?php
declare(strict_types = 1);

$server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($server, '127.0.0.1', 0);
socket_listen($server);
socket_getsockname($server, $address, $port);
echo $port . PHP_EOL;
flush();
socket_set_option($server, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
$client = socket_accept($server);
if ($client !== false) {
    $message = socket_read($client, 65535);
    socket_write($client, $message);
    socket_close($client);
}
socket_close($server);
