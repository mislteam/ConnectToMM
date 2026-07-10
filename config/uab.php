<?php

return [
    'version' => '1.0.0',
    'timeout' => (int) env('UAB_TIMEOUT_SECONDS', 20),
    'token_buffer_seconds' => (int) env('UAB_TOKEN_BUFFER_SECONDS', 30),
];
