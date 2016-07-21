<?php
namespace QuickMVC;

/**
 * QuickController
 */
class Controller
{
    private static $redirect = array();

    private $_response;
    private $_template;
    private $_view;

    /**
     * Do something before every action of this controller is executed
     *
     * $this->action is the called action
     * $this->args are the arguments
     */
    function before()
    {
        // This will advise the controller to wrap the output with the default template
        $this->setTemplate('default');
    }

    /**
     * Do something after every action of this controller is executed
     *
     * $this->action is the called action
     * $this->args are the arguments
     */
    function after()
    {
    }

    /**
     * Default action
     */
    function _index()
    {
    }

    /**
     * Renders the controller
     *
     * @return string The rendered html
     */
    function render()
    {
        if ($this->_response == null) {
            try {
                $this->before();
                call_user_func_array(array($this, '_' . $this->action), $this->args);

                // Dereference this object
                foreach (get_object_vars($this) as $var => $value) {
                    $$var = $value;
                }

                // Now render view
                ob_start();
                include VIEWS . $this->_view . '.php';
                $this->_response = ob_get_contents();
                ob_end_clean();

                $this->after();

                /*
                 * If at any point we get a redirect request, start over with the requested controller
                 *
                 * Hint: if you want to exchange information with the new controller (or change the template) use
                 * global variables ($_GLOBAL['template'] = &this->template())
                 */
            } catch (Redirect $e) {
                $controller = Controller::load(end(Controller::$redirect));
                $this->_response = $controller->render();
            }

            // Wrap with template if required
            if ($this->_template) {
                $this->_response = $this->_template->render($this->_response);
            }
        }
        return $this->_response;
    }

    public static function load($route)
    {
        // Parse the query string
        $args = explode('/', $route);

        // Parse requested controller (or fallback to index)
        if ($args[0] && file_exists(CONTROLLERS . $args[0] . '.php')) {
            $route = array_shift($args);
        } else {
            $route = 'index';
        }
        $controllerName = ucfirst($route) . 'Controller';

        // Load controller
        require_once CONTROLLERS . "$route.php";
        $controller = new $controllerName;

        // Parse requested action (or fallback to index)
        if ($args[0] && method_exists($controller, '_' . $args[0])) {
            $action = array_shift($args);
        } else {
            $action = 'index';
        }

        // Tell the controller the action
        $controller->action = $action;

        // Tell the controller the args
        $controller->args = $args;

        // Tell the controller the view
        $controller->setView($route . '/' . $action);

        return $controller;
    }

    public function setView($view) {
        $this->_view = $view;
    }

    public function setTemplate($template) {
        $this->_template = new Template($template);
    }

    public static function redirect($path = '')
    {
        ob_end_clean();
        header('Location: ' . URL::generate($path));
    }

    public static function redirectPost($path = '')
    {

        // To many redirects check
        if (count(self::$redirect) > Config::REDIRECT_MAX) {
            throw new RedirectException('Too many redirects');
        }

        // Place path into redirect stack
        self::$redirect[] = $path;

        // Exit rendering
        throw new Redirect;
    }

    public function __toString() {
        return $this->render();
    }
}