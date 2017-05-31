<?php

namespace QuickMVC;


class Migrator
{

    private static function getVersion() {
        try {
            $db = Database::get()->query("SELECT version FROM database_version");
            return $db->fetch(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {

            Database::get()->query("CREATE TABLE `database_version` (
              `version` int(11) NOT NULL,
              PRIMARY KEY (`version`)
            )");
            Database::get()->query("INSERT into database_version VALUES (0)");
            return 0;
        }

    }

    public static function migrate() {
        $version = self::getVersion();
        $migrations = self::getMigrationCount();
        if ($version < $migrations) {
            $i = 0;
            foreach(self::getGlob() as $filename)  {

                if ($i < $version) {
                    $i++;
                    continue;
                }

                include_once $filename;
                $php_file = file_get_contents($filename);
                $tokens = token_get_all($php_file);

                foreach ($tokens as $token) {
                    if (is_array($token)) {
                        if ($token[0] == T_CLASS) {
                            $class_token = true;
                        } else if ($class_token && $token[0] == T_STRING) {
                            $class_token = false;

                            $classname = $token[1];

                            $class = new \ReflectionClass($classname);

                            if ($class->implementsInterface('QuickMVC\Migration')) {
                                $classname::up();
                                self::increaseVersion();
                            }
                        }
                    }
                }
            }
        }
    }

    private static function getMigrationCount() {
        return count(self::getGlob());
    }

    private static function getPath() {
        return PATH.DIRECTORY_SEPARATOR."migrations";
    }

    private static function getGlob() {
        $arr = glob(self::getPath().DIRECTORY_SEPARATOR."*.php");
        sort($arr);
        return $arr;
    }

    private static function increaseVersion() {
        Database::get()->query("UPDATE database_version SET version = version + 1");
    }
}