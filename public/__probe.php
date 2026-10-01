<?php
header('Content-Type: text/plain; charset=utf-8');
echo "PHP: ".PHP_VERSION."\n";
echo "intl loaded: ".(extension_loaded('intl')?'YES':'NO')."\n";
echo "Locale class exists: ".(class_exists('Locale')?'YES':'NO')."\n";
echo "opcache enabled: ".(function_exists('opcache_get_status') && @opcache_get_status(false) ? 'YES':'NO/off')."\n";
$f = __DIR__.'/../system/CodeIgniter.php';
$src = file(  $f );
echo "CodeIgniter.php line 238: ".trim($src[237] ?? '')."\n";
echo "CodeIgniter.php line 240: ".trim($src[239] ?? '')."\n";
echo "loaded exts sample: ".implode(',', array_slice(array_filter(get_loaded_extensions(), fn($e)=>in_array(strtolower($e),['intl','mbstring','json','mysqli','sqlite3'])),0)) ."\n";
