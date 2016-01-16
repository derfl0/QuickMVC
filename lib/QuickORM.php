<?php

class QuickORM
{
    protected static $meta;

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

            // TODO: Replace with show columns from table
            $stmt = QuickDB::get()->prepare("SELECT * FROM " . static::DB_TABLE . " LIMIT 1");
            $stmt->execute();
            self::setMeta($stmt);
        }
        return static::$meta;
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
        return $object->setData($data);
    }
}