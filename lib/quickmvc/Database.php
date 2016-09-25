<?php
namespace QuickMVC;

/**
 * Database
 *
 * Access to your mysql Database
 */
class Database extends \PDO
{
    private static $instance;

    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new self('mysql:host=' . Config::DB_HOST
                . ';dbname=' . Config::DB_NAME
                . ';charset=utf8',
                Config::DB_USER,
                Config::DB_PASSWORD);
            if (Config::DEVELOPMENT_MODE) {
                self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            }
        }
        return self::$instance;
    }

    public static function storeDump($dumpname = "QuickDBDump.php")
    {
        $db = self::get();
        $dump = $db->getDump();
        $dumpfile = fopen($dumpname, "w") or die("Unable to open file!");
        fwrite($dumpfile, '<?php $dump = ');

        // now buffer export
        ob_start();
        var_export($dump);
        $export = ob_get_contents();
        ob_end_clean();

        fwrite($dumpfile, $export);
        fwrite($dumpfile, ';');
    }

    public static function restoreDump($dumpname = "QuickDBDump.php") {

        if (file_exists($dumpname)) {
            $db = self::get();
            include $dumpname;
            foreach ($dump as $table => $arr) {

                // Drop table if existed
                $db->query("DROP TABLE IF EXISTS $table");

                // Create
                $db->query($arr['table']);

                // If we also dumped values
                if ($arr['values']) {
                    $size = count($arr['values'][0]);
                    $prepared = substr(str_repeat("?,", $size), 0, -1);
                    $insert = $db->prepare("INSERT INTO $table VALUES ($prepared)");

                    // Insert values
                    foreach ($arr['values'] as $val) {
                        $insert->execute($val);
                    }
                }
            }
        }
    }

    public function getDump()
    {
        $allTables = $this->query('SHOW TABLES');
        while ($table = $allTables->fetchColumn()) {

            $dump[$table]['table'] = $this->query("SHOW CREATE TABLE `$table`")->fetchColumn(1);

            // Values
            $val = $this->query("SELECT * FROM `$table`");
            $dump[$table]['values'] = $val->fetchAll(\PDO::FETCH_NUM);
        }
        return $dump;
    }
}
