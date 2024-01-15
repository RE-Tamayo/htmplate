
Htmplate: Uncomplicated template engine embedded in HTML. No extra languages. Just use and simplify your web development.

How to use

Install using composer.
```bash
composer require retamayo/htmplate
```

Include autoloader
```php
include __DIR__ . "../vendor/autoload.php";
```

Use namespace
```php
use Retamayo\Htmplate\Htmplate;
```

Instantiate Htmplate
```php
$htmp = new Htmplate();
```

Use render method
```php
$htmp->render('view_name', 'data');
```

Take note that the render method looks for the view folder in your basepath, and only accepts html files.
