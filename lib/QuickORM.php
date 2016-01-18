<?php

class QuickORM
{
    protected static $meta;
    protected static $storage;

    public static function findBy($key, $value)
    {
        $stmt = QuickDB::get()->prepare("SELECT * FROM " . static::DB_TABLE . " WHERE $key=?");
        $stmt->execute(array($value));
        self::setMeta(get_called_class(), $stmt);
        return $stmt->fetchObject(get_called_class());
    }

    public function store()
    {
        $meta = static::getMeta(get_called_class());

        // Prepare SQL query
        $prepared = substr(str_repeat("?,", count($meta)), 0, -1);
        $stmt = QuickDB::get()->prepare("REPLACE INTO " . static::DB_TABLE . " VALUES ($prepared)");
        // Prepare values
        foreach ($meta as $m) {
            $params[] = $this->$m['name'];
        }
        // Execute
        $stmt->execute($params);
    }

    private static function setMeta($class, $stmt)
    {
        if (!static::$meta[$class]) {
            while ($meta = $stmt->getColumnMeta($i++)) {
                static::$meta[$class][] = $meta;
            }
        }
    }

    private static function getMeta($class)
    {
        if (!static::$meta[$class]) {
            // Fetch meta
            $stmt = QuickDB::get()->prepare("SHOW COLUMNS FROM " . static::DB_TABLE);
            $stmt->execute();
            while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
                static::$meta[$class][] = array('name' => $col['Field']);
            }
        }
        return static::$meta[$class];
    }

    public static function find($where = '1=1', $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $stmt = QuickDB::get()->prepare("SELECT * FROM " . static::DB_TABLE . " WHERE $where");
        $stmt->execute($params);
        static::$storage = $stmt;
        return $stmt;
    }

    public static function fetch()
    {
        return static::$storage->fetchObject(get_called_class());
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

    public static function deleteBy($where = '1=1', $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }
        $stmt = QuickDB::get()->prepare("DELETE FROM " . static::DB_TABLE . " WHERE $where");
        $stmt->execute($params);
    }
}