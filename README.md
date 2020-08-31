PHP-TheNounProject
===

Simple PHP wrapper for The Noun Project's API, using PECL's OAuth extension for
authentication.

### Sample Call

``` php
require_once '/path/to/TheNounProject.class.php';
$key  = '*****';
$secret = '*****';
$theNounProject = new TheNounProject($key, $secret);
$limit = 10;
$options = compact('limit');
$icons = $theNounProject->getIconsByTerm('happy', $options);
print_r($icons);
exit(0);
```
