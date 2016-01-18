<?php

class QuickORM
{
    protected static $meta;
    protected static $storage;
    protected static $db;

    public static function getDB() {
        if (!isset(self::$db)) {
            self::$db = QuickDB::get();
        }
        return self::$db;
    }

    public function __construct($data = null) {

        // If we got an array load that data
        if (is_array($data)) {
            $this->setData($data);
        } else {

            // Else load parameters
            foreach (func_get_args() as $num => $value) {
                $field = static::getMeta()[$num]['Field'];
                $this->$field = $value;
            }
        }
    }

    public static function find($key, $value = null)
    {
        // if we search pk
        if (!isset($value)) {
            $sql = "SELECT * FROM " . static::DB_TABLE . " WHERE ".static::getPKWhere();
                $value = $key;
        } else {
            $sql = "SELECT * FROM " . static::DB_TABLE . " WHERE $key=?";
        }

        // array fallback for key
        if (!is_array($value)) {
            array($value);
        }

        $stmt = self::getDB()->prepare($sql);
        $stmt->execute(array($value));
        return $stmt->fetchObject(get_called_class());
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
    }

    private static function getMeta()
    {
        if (!static::$meta[static::class]) {
            // Fetch meta
            $stmt = self::getDB()->prepare("SHOW COLUMNS FROM " . static::DB_TABLE);
            $stmt->execute();
            static::$meta[static::class] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return static::$meta[static::class];
    }

    private static function getPrimaryKey() {
        foreach (static::getMeta() as $col) {
            if ($col['Key'] == 'PRI') {
                $result[] = $col['Field'];
            }
        }
        return $result;
    }

    private static function getPKWhere() {
        foreach (static::getPrimaryKey() as $pk) {
            $sql[] = " $pk = ? ";
        }
        return join(' AND ', $sql);
    }

    public static function findAll($where = '1=1', $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $stmt = QuickDB::get()->prepare("SELECT * FROM " . static::DB_TABLE . " WHERE $where");
        $stmt->execute($params);
        static::$storage[static::class] = $stmt;
        return $stmt;
    }

    public static function fetch()
    {
        return static::$storage[static::class]->fetchObject(get_called_class());
    }

    public static function fetchAll()
    {
        while ($obj = static::fetch()) {
            $result[] = $obj;
        }
        return $obj;
    }

    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public static function create($data)
    {
        $object = new static();
        $object->setData($data);
        return $object;
    }

    public function delete() {
        $stmt = self::getDB()->prepare("DELETE FROM " . static::DB_TABLE . " WHERE ".static::getPKWhere());
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
}