<?php

class QuickORM
{
    protected static $meta;

    protected static $storage;

    public static function findBy($key, $value)
    {
        $stmt = QuickDB::get()->prepare("SELECT * FROM " . static::DB_TABLE . " WHERE $key=?");
        $stmt->execute(array($value));
        self::setMeta($stmt);
        return $stmt->fetchObject(get_called_class());
    }

    public function store()
    {
        $meta = static::getMeta();

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

    private static function setMeta($stmt)
    {
        if (!static::$meta) {
            while ($meta = $stmt->getColumnMeta($i++)) {
                static::$meta[] = $meta;
            }
        }
    }

    private static function getMeta()
    {
        if (!static::$meta) {

            // Fetch meta
            $stmt = QuickDB::get()->prepare("SHOW COLUMNS FROM " . static::DB_TABLE);
            $stmt->execute();
            while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
                static::$meta[] = array('name' => $col['Field']);
            }
        }
        return static::$meta;
    }

    public static function find($where = '1=1', $params = array()) {
        $stmt = QuickDB::get()->prepare("SELECT * FROM " . static::DB_TABLE . " WHERE $where");
        $stmt->execute($params);
        static::$storage = $stmt;
    }

    public static function fetch() {
        return static::$storage->fetchObject(get_called_class());
    }

    public static function fetchAll() {
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
}