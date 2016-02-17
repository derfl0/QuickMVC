# QuickMVC
Quick and dirty MVC Framework for small PHP Projects
It is designed to be as easy as possible, but still provide a lot of useful features

## Installation
Download and move to your webserver ... and you're done.

## QuickStart
Now lets start developing

### MVC in a nutshell
Your application can be called with yourpath/controller/action/param1/param2/...

Controller are to be placed in app/controllers. The file needs to have the controller name.
The class has to be called NameController
e.g. IndexController in app/controller/index.php

For each action there has to be a function in the controller with the same name with an underscore.
e.g. the function _index() represents our Index action. yourpath/index/index will call the index controller and start
the index action.
Parameters for the view just have to be stored in the controller
$this->something = "SomeValue";

Default view for a route is app/views/[controller]/[action]. You can also set the view in a action by using $this->setView($viewname);
Parameter from the controller can be accessed directly
<?= $something ?> will output "SomeValue"
Hint: http://php.net/manual/en/control-structures.alternative-syntax.php

### Database
Rename lib/QuickConfig.dist.php to lib/QuickConfig.php and insert database connection
Now use QuickDB::get() to retrieve your PDO Object

```php
<?php
    $stmt = QuickDB::get()->prepare('SELECT * FROM mytable');
    $stmt->execute();
    var_dump($stmt->fetchAll());
```

While we have the constant DEV set to true in index.php QuickMVC will keep a complete Dump of your DB in QuickDBDump.php to never get you out of sync while developing. Since it will be restored before every execution in development deactivate this feature when your database is to big.

### ORM
A better way to deal with database is ORM.
Let's assume we have a ta

### Redirecting
To redirect a request to another controller use:
QuickController::redirect('controller/action/params');
This will do a complete redirect and keep no data

If it is necessary to provide data (as well as $_REQUEST) use:
QuickController::redirectPost('controller/action/params');
Use $GLOBALS to transfer data to the new controller
