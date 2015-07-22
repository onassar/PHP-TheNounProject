# PHP-TheNounProject

Simple PHP wrapper for The Noun Project's API, using PECL's OAuth extension for authentication.

### Sample Call

``` php
<?php
    require_once '/path/to/TheNounProject.class.php';
    $key  = '*****';
    $secret = '*****';
    $theNounProject = (new TheNounProject($key, $secret));
    $icons = $theNounProject->getIconsByTerm(
        'happy',
        array('limit' => 10)
    );
    print_r($icons);
    exit(0);

```
