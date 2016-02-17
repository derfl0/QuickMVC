<?php

/**
 * Class QuickORM
 *
 * Parent class for Quick Object Relationship Mapping
 *
 * Example:
 *
 * class User extends QuickORM {
 *  const DB_TABLE = 'users';
 * }
 *
 * Is enough to get all the features of the ORM
 */
class QuickORM
{
    protected static $meta;
    protected static $storage;
    protected static $db;

    /**
     * This singleton is here in the purpose to be able to use QuickORM without the rest of the framework. Just give
     * it some other DB in here.
     *
     * @return PDO Will return the required database for operations
     */
    protected static function getDB()
    {
        if (!isset(self::$db)) {
            self::$db = QuickDB::get();
        }
        return self::$db;
    }

    public function __construct($data = null)
    {

        // If we got data then set it
        if (func_num_args()) {
            $this->setData(is_array($data) ? $data : func_get_args());
        }
    }

    /**
     * Magic method to implement auto value storage
     *
     * HowTo: If you want an object to store an array of users from the database define a method getUser and let it
     * return the result. Call it magically by $object->user to prevent loading it everytime from the db
     *
     * @param $val
     * @return mixed
     */
    public function __get($val)
    {
        if (!isset($this->$val) && method_exists(get_called_class(), 'get' . ucfirst($val))) {
            $this->$val = call_user_func(array($this, 'get' . ucfirst($val)));
        }
        return $this->$val;
    }

    public static function find($key, $value = null)
    {
        // if we search pk
        if (!isset($value)) {
            $sql = "SELECT * FROM " . static::DB_TABLE . " WHERE " . static::getPKWhere() . " LIMIT 1";
            $value = $key;
        } else {
            $sql = "SELECT * FROM " . static::DB_TABLE . " WHERE $key=? LIMIT 1";
        }

        // array fallback for key
        if (!is_array($value)) {
            array($value);
        }

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(array($value));
        return $stmt->fetchObject(get_called_class());
    }

    public static function findAll($where = '1=1', $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $stmt = QuickDB::get()->prepare("SELECT * FROM " . static::DB_TABLE . " WHERE $where");
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());
        static::$storage[static::class] = $stmt;
        return $stmt;
    }

    public function store()
    {
        $meta = static::getMeta(get_called_class());

        // Prepare SQL query
        $prepared = substr(str_repeat("?,", count($meta)), 0, -1);
        $stmt = self::getDB()->prepare("REPLACE INTO " . static::DB_TABLE . " VALUES ($prepared)");
        // Prepare values
        foreach ($meta as $m) {
            $params[] = $this->$m['Field'];
        }
        // Execute
        $stmt->execute($params);

        // Check for auto increment
        if ($col = static::getAutoIncrement()) {
            $this->$col = self::getDB()->lastInsertId();
        }

    }


    public function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->setAttributes($key, $value);
        }
    }

    public function setAttributes($attr, $value)
    {
        // Mapping in case we want to address out attribut numeric
        if (is_numeric($attr)) {
            $attr = static::getMeta()[$attr]['Field'];
        }
        $this->$attr = $value;
    }

    public static function create($data)
    {
        $object = new static();
        $object->setData(is_array($data) ? $data : func_get_args());
        $object->store();
        return $object;
    }

    public function delete()
    {
        $stmt = self::getDB()->prepare("DELETE FROM " . static::DB_TABLE . " WHERE " . static::getPKWhere());
        foreach (static::getPrimaryKey() as $key) {
            $params[] = $this->$key;
        }
        $stmt->execute($params);
    }

    public static function deleteAll($where = '1=1', $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }
        $stmt = self::getDB()->prepare("DELETE FROM " . static::DB_TABLE . " WHERE $where");
        $stmt->execute($params);
    }

    ############# UTILITY PART #############

    public static function getStorage()
    {
        return static::$storage[static::class];
    }

    public static function fetch()
    {
        return static::getStorage()->fetchObject(get_called_class());
    }

    public static function fetchAll()
    {
        return static::getStorage()->fetchAll();
    }

    ############# META PART #############

    protected static function getMeta()
    {
        // If we're productive and meta wasn't loaded connect to session meta
        if (!static::$meta && !DEV) {
            static::$meta = &$_SESSION['db_meta'];
        }

        // If meta for this table was not already loaded, load it now
        if (!static::$meta[static::class]) {
            $stmt = self::getDB()->prepare("SHOW COLUMNS FROM " . static::DB_TABLE);
            $stmt->execute();
            static::$meta[static::class] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return static::$meta[static::class];
    }

    protected static function getPrimaryKey()
    {
        foreach (static::getMeta() as $col) {
            if ($col['Key'] == 'PRI') {
                $result[] = $col['Field'];
            }
        }
        return $result;
    }

    /**
     * @return String Name of the auto increment column
     */
    protected static function getAutoIncrement() {
        foreach (static::getMeta() as $col) {
            if ($col['Extra'] == 'auto_increment') {
                return $col['Field'];
            }
        }
    }

    protected static function getPKWhere()
    {
        foreach (static::getPrimaryKey() as $pk) {
            $sql[] = " $pk = ? ";
        }
        return join(' AND ', $sql);
    }
}