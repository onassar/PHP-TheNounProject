# PHP-TheNounProject

Simple PHP wrapper for The Noun Project's API, using PECL's OAuth extension for authentication.

### Sample Call

``` php
<?php
    require_once '/path/to/TheNounProject.class.php';
    $key  = '*****';
    $secret = '*****';
    $nounProject = (new TheNounProject($key, $secret));
    print_r($nounProject->getIconsByTerm('happy', array('limit' => 10)));
    exit(0);

```
