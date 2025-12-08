<?php
require_once 'common.php';

// Imposta il codice di stato HTTP 500 (Internal Server Error)
http_response_code(500);

echo caricaPagina('500.html');
?>