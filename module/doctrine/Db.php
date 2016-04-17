<?php

class Db
{

    /**
     * @Inject
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * Executes an SQL statement, returning a result set as a Statement object.
     * @param string $sql
     * @return mixed
     */
    public function sql($sql)
    {
        return $this->em->getConnection()->query($sql)->fetchAll();
    }

    /**
     * Executes an SQL statement and return the number of affected rows.
     * @param string $sql
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function exec($sql)
    {
        return $this->em->getConnection()->exec($sql);
    }

    /**
     * Install all sqls
     * @param string $prefix
     * @return array
     */
    public function install($prefix = null)
    {
        if (is_null($prefix))
            $prefix = 'ver-';

        $files = glob(ABSPATH . 'sql/' . $prefix . '*.sql');

        $r = array();
        foreach ($files as $file) {
            $lines = 1;
            $sqls = $this->split(file_get_contents($file));
            try {
                foreach ($sqls as $sql) {
                    if (trim($sql))
                        self::exec($sql);
                    ++$lines;
                }
            } catch (Exception $e) {
                if (strpos(strtolower($e->getMessage()), 'duplicate') === false) {
                    $r[$file]['line'] = $lines;
                    $r[$file]['message'] = $e->getMessage();
                }
            }
        }

        return $r;
    }

    /**
     * Split sql
     * @param $sql
     * @return array
     */
    protected function split($sql)
    {
        $sql = str_replace("\r\n", "\n", $sql);
        $sql = str_replace("\r", "\n", $sql);
        $sql = preg_replace("/\n{2,}/", "\n\n", $sql);

        $r = array();
        $buf = explode(";\n", $sql);
        foreach ($buf as $row) {
            $r[] = $row;
        }
        return $r;
    }

}
