#!/usr/bin/env php
<?php

// Tests the regex to see if it filters directory traversal attacks safely
$dirRoot = 'root';
$dirDocroot = $dirRoot.'/parent/child/docroot';

// Options
$regex = null;

// Stdin
$paths = [];
//stream_set_blocking(STDIN, 0);
$input = stream_get_contents(STDIN);
$paths = array_filter(explode(PHP_EOL, $input));

// Test method
foreach ($paths as $path) {
    if ($regex === null || !preg_match($regex, $path)) {
        echo 'WARNING: Passed validation "'.$path.'".'.PHP_EOL;
        $filename = __DIR__.'/'.$dirDocroot.'/'.$path;
        @mkdir(dirname($filename), 0777, true);
        if ($handle = fopen($filename, 'wb')) {
            echo 'WARNING: Got handle "'.$filename.'".'.PHP_EOL;
            if (fwrite($handle, 'hacked'.PHP_EOL)) {
                echo 'WARNING: Wrote file "'.$filename.'".'.PHP_EOL;
            }
            fclose($handle);
        }
    }
}

// Detection
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/'.$dirRoot), RecursiveIteratorIterator::CHILD_FIRST);
foreach ($iterator as $path) {
    // Skip directory and parent.
    if ($path->getFilename() === '.' || $path->getFilename() === '..') {
        continue;
    }

    $relativePathname = str_replace(__DIR__.'/', '', $path->getPathname());

    // Skip docroot directory hierachy.
    if (in_array($relativePathname, [
          'root',
          'root/parent',
          'root/parent/child',
          'root/parent/child/docroot'
    ])) {
        continue;
    }

    // Skip files in docroot
    if (strpos($relativePathname, 'root/parent/child/docroot') === 0) {
        continue;
    }

    echo 'EMERG: Hacked path detected "'.$relativePathname.'"'.PHP_EOL;
}
exit;


$directory = new RecursiveDirectoryIterator(__DIR__.'/'.$dirRoot, \FilesystemIterator::FOLLOW_SYMLINKS);
$filter = new RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) {
    // Skip directory and parent.
    if ($current->getFilename() === '.' || $current->getFilename() === '..') {
      return FALSE;
    }
    $relativePathname = str_replace(__DIR__.'/', '', $current->getPathname());
    // Skip docroot directory hierachy.
    if (in_array($relativePathname, [
          'root',
          'root/parent',
          'root/parent/child',
          'root/parent/child/docroot'
    ])) {
        return FALSE;
    }
    // Return anything not in the docroot.
    echo (strpos($relativePathname, 'root/parent/child/docroot') === false ? 'Y: ': 'N: ').$relativePathname.PHP_EOL;
    return strpos($relativePathname, 'root/parent/child/docroot') === false;
});
$iterator = new \RecursiveIteratorIterator($filter);
foreach ($iterator as $info) {
    echo 'EMERG: Hacked path detected "'.$info->getPathname().PHP_EOL.'". Press any key to continue...';
}

// Cleanup
if (isset($filename)) {
    unlink($filename);
}

