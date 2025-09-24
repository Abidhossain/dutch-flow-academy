A simple PHP library for interfacing with the Moneybird API.
Documentation: http://developer.moneybird.com/.

Example usage:

```php
require("moneybird2_api.php");
$mb_api = new Moneybird2Api("access_token_here");
if ($mb_api->isConnectionWorking()) {
    echo("Connected to the Moneybird API");
} else {
    echo("Could not connect to the Moneybird API. Errors:<br/><br/>");
    echo(implode("<br/>", $mb_api->getErrors()));
}
```