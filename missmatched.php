<?php
function findMismatchedReferences($directory) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $mismatchedReferences = [];
    $allFiles = [];

    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getRealPath());
            preg_match_all('/(include|require|header)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $content, $matches);
            foreach ($matches[2] as $match) {
                if (!file_exists($directory . '/' . $match)) {
                    $mismatchedReferences[] = $file->getRealPath() . ' -> ' . $match;
                }
            }
            $allFiles[] = $file->getRealPath();
        }
    }

    return [$mismatchedReferences, $allFiles];
}

function findUselessFiles($directory, $allFiles) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $uselessFiles = [];

    foreach ($files as $file) {
        if ($file->isFile() && !in_array($file->getRealPath(), $allFiles)) {
            $uselessFiles[] = $file->getRealPath();
        }
    }

    return $uselessFiles;
}

$directory = __DIR__; // Change this to your project directory
list($mismatchedReferences, $allFiles) = findMismatchedReferences($directory);
$uselessFiles = findUselessFiles($directory, $allFiles);

echo "Mismatched References:\n";
print_r($mismatchedReferences);

echo "\nUseless Files:\n";
print_r($uselessFiles);
?>