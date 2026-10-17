<?php
// Manual Composer Installation Script for WAMP

echo "<h2>Installing Composer Dependencies...</h2>";

// Download composer.phar
$composerUrl = 'https://getcomposer.org/composer-stable.phar';
$composerFile = __DIR__ . '/composer.phar';

if (!file_exists($composerFile)) {
    echo "<p>Downloading composer.phar...</p>";
    $composerContent = file_get_contents($composerUrl);
    if ($composerContent) {
        file_put_contents($composerFile, $composerContent);
        echo "<p style='color: green;'>✓ composer.phar downloaded</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to download composer.phar</p>";
        exit;
    }
} else {
    echo "<p style='color: green;'>✓ composer.phar already exists</p>";
}

// Run composer install
echo "<p>Running composer install...</p>";
$phpPath = 'C:\wamp64\bin\php\php8.1.31\php.exe'; // Adjust to your PHP version
$command = "\"$phpPath\" \"$composerFile\" install --no-dev --optimize-autoloader";

echo "<p>Command: $command</p>";
echo "<pre>";
passthru($command, $returnCode);
echo "</pre>";

if ($returnCode === 0) {
    echo "<h2 style='color: green;'>✓ Composer install completed successfully!</h2>";
    echo "<p><a href='index.php'>Go to application</a></p>";
} else {
    echo "<h2 style='color: red;'>✗ Composer install failed</h2>";
    echo "<p>Return code: $returnCode</p>";
}
?>
