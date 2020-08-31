# PHP-TheNounProject
PHP SDK for running queries against the millions of icons provided by
[The Noun Project](https://thenounproject.com/).

### Sample Usage

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
