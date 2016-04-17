<?php

namespace Controller;

use Db;
use Conf;
use Error403;
use Doctrine\ORM\Tools\SchemaTool;

class Doctrine
{

    /**
     * @Inject
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @Inject
     * @var \DbCommand
     */
    protected $db;

    /**
     * @Route("nc/db/dump/{token}")
     * @param string $token
     * @return array
     */
    public function dump($token)
    {
        $this->auth($token);

        $schemaTool = new SchemaTool($this->em);
        $cmf = $this->em->getMetadataFactory();
        $sqls = $schemaTool->getUpdateSchemaSql($cmf->getAllMetadata(), false);

        return array('sqls' => $sqls);
    }

    /**
     * @Route("nc/db/reset/{token}")
     * @param string $token
     * @return array
     */
    public function reset($token)
    {
        $this->auth($token);

        $this->db->dropAll();
        return array("error" => $this->install());
    }

    /**
     * @Route("nc/db/update/{token}")
     * @param string $token
     * @return array
     */
    public function update($token)
    {
        $this->auth($token);
        return array("error" => $this->install());
    }

    /**
     * Install sqls
     * @param string $prefix
     * @return array
     */
    public function install($prefix = null)
    {
        return $this->db->install($prefix);
    }

    /**
     * @param $token
     * @throws Error403
     */
    private function auth($token)
    {
        if (Conf::get('nc.token') != $token)
            throw new Error403;
    }
}
