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
Let's assume we have a table called users with id (primary key and auto_increment) username and password. Put the following Code into app/models/User.php

```php
<?php
class User extends QuickORM {
    const DB_TABLE = 'users';
}
```

That's all. Now we can work with our User Object. Let's do some CRUD

#### Create

```php
<?php
// Let's create a user without storing in the database
$user = new User(array('username' => 'Peter', 'password' => 'secret');

// We could also create it with the following code:
$user = new User(null, 'Peter' 'secret');

// Now let's store Peter
$user->store();

// At this point Peter will get an auto assigned id which is stored in our $user
echo $user->id; // 1

// Now let's create a second user in the database
$user2 = User::create(null, 'Lois' 'topsecret'); // $user2 will be our new created user object

```

#### Read

```php
<?php
$user = User::find(2) // Will find the user with the primary key 2 (in our case: Lois)
$user = User::find('username', 'Peter); // Will find the user with the username Peter (in our case: Peter)

// Now find multiple ones
$users = User::findAll(); // Will find all users from the database
$users = User::findAll('id < 3'); // Will find all users with an id smaller than 3
$users = User::findAll('id < ? AND username LIKE ?', array(10, 'P%')); // Will find all users with an id below 10 and a username starting with P

// Do something with our users
foreach($users as $user) {
    echo $user->username;
}

```
Note that findAll will always returns a PDO Statment (set to fetchmode object) which can be used, thanks to the interface, in foreach or while.
You can also use
```php
<?php
$userarray = $users->fetchAll();
```
to fetch all Users into an Array.
There is one (!) internal storage in every ORM which always contains the last findAll query. (Once the elements where fetched, it is also empty!) Use ::fetch() or ::fetchAll() to retrieve an element.
```php
<?php
User::findAll('LENGTH(password) > 4 ');
while ($user = User::fetch()) {
    // do something
}
```

#### Update

```php
<?php
$user = User::find(2);
$user->username = 'Stewie';
$user->store();
```

Overwrite the username Peter to Stewie


#### Delete

```php
<?php
$user = User::find(2);
$user->delete(); // Delete Stewie from the database. $user object (in php) is still valid AND (!) could be stored again if required
```

```php
<?php
User::deleteAll('username LIKE ?', array('L%')); // Delete all users where the username starts with an L
```

### Templates

Since most of the pages of our application will look the same we can use templates. Our Controller has a template set in the before function. After successfully parsing the view it will wrap the whole code in the layout. Use for example to have different layouts for mobile and desktop.
If you just want the view use $controller->setTemplate(null);

### Redirecting
To redirect a request to another controller use:
QuickController::redirect('controller/action/params');
This will do a complete redirect and keep no data

If it is necessary to provide data (as well as $_REQUEST) use:
QuickController::redirectPost('controller/action/params');
Use $GLOBALS to transfer data to the new controller
