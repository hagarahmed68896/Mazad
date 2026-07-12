<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache cleared successfully!";
} else {
    echo "OPCache is not enabled or function does not exist.";
}
