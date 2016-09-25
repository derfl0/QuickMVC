<?php

namespace QuickMVC;

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
 * Enough to get all the features of the ORM
 */
class ORM
{
    /**
     * @var Meta information of tables
     */
    protected static $meta;

    /**
     * @var Storage for the last findAll Query
     */
    protected static $storage;

    /**
     * @var Singleton database instance
     */
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
            self::$db = Database::get();
        }
        return self::$db;
    }

    /**
     * Constructor of ORM. Will pass data to set data
     *
     * Data can be passed like
     *
     * new QuickORM('first', null, 2);
     * new QuickORM(array('first', null, 2));
     * new QuickORM(array(1 => 'first', 2 => null, 3 => 2));
     * new QuickORM(array('firstDBCol' => 'first', 'secondDBCol' => null, 'thirdDBCol' => 2));
     *
     * @param mixed $data Entity data
     */
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

    ############# CRUD ###################

    /**
     * Will create a new entity (also in database)
     *
     * QuickORM::create('first', null, 2);
     * QuickORM::create(array('first', null, 2));
     * QuickORM::create(array(1 => 'first', 2 => null, 3 => 2));
     * QuickORM::create(array('firstDBCol' => 'first', 'secondDBCol' => null, 'thirdDBCol' => 2));
     *
     * @param $data Data for new entity
     *
     * @return static The created entity
     */
    public static function create($data)
    {
        $object = new static();
        $object->setData(is_array($data) ? $data : func_get_args());
        $object->store();
        return $object;
    }

    /**
     * Find a single entity in the database
     *
     * QuickORM::find(5); Will select the entity with the primary key 5
     *
     * QuickORM::find('id', 6); Will find the entity where the column id has the value 6
     *
     * @return static The entity
     */
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

    /**
     * Find all entities that match some conditions
     *
     * QuickORM::findAll('id > 5'); Will find all entities where the id is bigger than 5
     * QuickORM::findAll('id > ?', array(5)); Same as above but with prepared query
     *
     * QuickORM::findAll('id > ? AND date < ?', array(5, time())); Will find all entities where the id is bigger than 5
     * and the date is below the current servertime
     *
     * @param string $where SQL Like where string
     * @param array $params Params to use with Prepared Statment
     * @return PDOStatement PDO result set ready to retrieve objects
     */
    public static function findAll($where = '1=1', $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $stmt = Database::get()->prepare("SELECT * FROM " . static::DB_TABLE . " WHERE $where");
        $stmt->execute($params);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, get_called_class());
        static::$storage[static::class] = $stmt;
        return $stmt;
    }

    /**
     * Stores the entity to the database. Will also set the autoincrement if there is one
     */
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

    /**
     * Deletes this entity
     */
    public function delete()
    {
        $stmt = self::getDB()->prepare("DELETE FROM " . static::DB_TABLE . " WHERE " . static::getPKWhere());
        foreach (static::getPrimaryKey() as $key) {
            $params[] = $this->$key;
        }
        $stmt->execute($params);
    }

    /**
     * Deletes all entities, that match the where condition
     *
     * QuickORM::deleteAll('id > 5'); Will delete all entities where the id is bigger than 5
     * QuickORM::deleteAll('id > ?', array(5)); Same as above but with prepared query
     *
     * QuickORM::deleteAll('id > ? AND date < ?', array(5, time())); Will delete all entities where the id is bigger
     * than 5 and the date is below the current servertime
     *
     * @param string $where SQL Like where string
     * @param array $params Params to use with Prepared Statment
     * @return int Number of deleted objects
     */
    public static function deleteAll($where = '1=1', $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }
        $stmt = self::getDB()->prepare("DELETE FROM " . static::DB_TABLE . " WHERE $where");
        return $stmt->execute($params);
    }

    ############# DATA SETTER PART ##########

    /**
     * Sets data of the entity
     *
     *
     *
     * @param array $data New data
     */
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
            static::$meta[static::class] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
    protected static function getAutoIncrement()
    {
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