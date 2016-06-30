<?php

class DbCommand extends Db
{

    /**
     * @var string
     */
    protected $driver;

    public function __construct()
    {
        $driver = $this->em->getConnection()->getDriver()->getName();
        $this->driver = str_replace('pdo_', '', $driver);
    }

    /**
     * Postgress DropAll
     */
    public function pgsqlDropAll()
    {
        $schemas = Db::sql("SELECT format('DROP SCHEMA %I CASCADE', nspname) FROM pg_namespace join pg_user on(usesysid=nspowner) where user=usename;");
        foreach ($schemas as $sql) {
            $this->exec($sql['format']);
        }

        $this->exec("CREATE SCHEMA public");
    }

    /**
     * MySql DropAll
     */
    public function mysqlDropAll()
    {
        $tables = $this->sql('SHOW TABLES');
        $this->em->getConnection()->beginTransaction();
        $this->em->getConnection()->setAutoCommit(false);

        $this->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $this->exec('DROP TABLE IF EXISTS ' . current($table) . ' CASCADE');
        }

        $elements = $this->sql('SHOW FULL TABLES');
        foreach ($elements as $element) {
            $this->exec("DROP {$element['Table_type']} IF EXISTS " . current($element) . " CASCADE");
            var_dump(current($element));
        }
        $this->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $method = $this->driver . ucfirst($method);
        if (method_exists($this, $method)) {
            return $this->$method($arguments);
        }

        throw new \BadMethodCallException("Call to undefined method DbCommand::" . $method);
    }

}
