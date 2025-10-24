<?php

// Script to replace SchoolYear::create with updateOrCreate for parallel test safety

$files = glob('tests/Feature/**/*Test.php');

foreach ($files as $file) {
    $content = file_get_contents($file);

    // Replace SchoolYear::create with updateOrCreate
    // updateOrCreate uses name as unique key and updates/creates accordingly
    $content = preg_replace(
        '/\\\\App\\\\Models\\\\SchoolYear::create\(\[/',
        '\\App\\Models\\SchoolYear::updateOrCreate([\'name\' => $1], [',
        $content
    );

    // Simpler approach: just replace create with updateOrCreate
    // updateOrCreate will use the first unique field it finds (name)
    $pattern = '/(\\\\App\\\\Models\\\\SchoolYear)::create\(\[\s*\'name\'\s*=>\s*([^,]+),/';
    $replacement = '$1::updateOrCreate([\'name\' => $2], [\'name\' => $2,';

    $content = preg_replace($pattern, $replacement, $content);

    file_put_contents($file, $content);
}

echo 'Fixed '.count($files)." files\n";
