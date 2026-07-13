<?php
echo "\n";
echo 'HATA: ' . get_class($exception) . "\n";
echo $exception->getMessage() . "\n";
echo $exception->getFile() . ':' . $exception->getLine() . "\n\n";
echo $exception->getTraceAsString() . "\n";
