<?php

$versionFile = base_path('../VERSION');
$fromFile = is_readable($versionFile) ? trim((string) file_get_contents($versionFile)) : '';

return [
    'version' => $fromFile !== '' ? $fromFile : env('FANFOR_RELEASE_VERSION', 'dev'),
];
