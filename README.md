# QuickMVC
Quick and dirty MVC Framework for small PHP Projects

## Setup
Simply edit "RewriteBase /QuickMVC" to whatever your base path is

## How to
In QuickMVC routing is done automatically.
Your application can be called with yourpath/controller/action/param1/param2/...

### Controller
Controller are to be placed in app/controller. The file needs to have the controller name.
The class has to be called NameController
e.g. IndexController in app/controller/index.php

### Action
For each action there has to be a function in the controller with the same name.
Parameters for the view just have to be stored in the controller
$this->something = "SomeValue";

### View
Default view for a route is app/view/[controller]/[action]
Parameter from the controller can be accessed directly
<?= $something ?> will output "SomeValue"