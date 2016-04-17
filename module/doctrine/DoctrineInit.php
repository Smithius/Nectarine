<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 * @Boot(order="1")
 * Class Home
 */
class DoctrineInit
{
    /**
     * @Inject
     * @var \Di
     */
    private $di;

    /**
     * Home constructor.
     */
    public function __construct()
    {
        $this->createEntityManager();
        $this->addResolverExtension();
    }

    private function createEntityManager()
    {
        $conn = array(
            'driver' => Conf::get('db.driver'),
            'host' => Conf::get('db.host'),
            'user' => Conf::get('db.user'),
            'password' => Conf::get('db.pass'),
            'dbname' => Conf::get('db.name'),
        );

        $paths = App::modules("Model");
        $config = Setup::createAnnotationMetadataConfiguration($paths, DEBUG);
        $entityManager = EntityManager::create($conn, $config);
        $this->di->set($entityManager, array(), 'em');
    }

    private function addResolverExtension()
    {
        $this->di->get('resolver')->addExtension('doctrine', function ($type, $value, $param) {
            if (class_exists($type) && strpos(trim($type, '\\'), "Model") === 0) {
                $value = $this->di->get('em')->find($type, $value);
                if (is_null($value))
                    throw new Error404("Entity $type with id $value not found");

                return $value;
            }

            return false;
        });
    }
}
