REST cURL for PHP
=================

Classe para requisições REST utilizando cURL

Exemplo
-------

```php
<?php
  require_once('REST-cURL.php');

  $url = "http://dotenorio.com";

  $restCurl = new RestCurl();
  echo $restCurl->GET($url);
?>
```
