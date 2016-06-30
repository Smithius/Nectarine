<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

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

    public function __construct()
    {
        $this->createEntityManager();
        $this->addResolverExtension();
    }

    /**
     * Create Doctrine Entity Manager
     *
     * @throws \Doctrine\ORM\ORMException
     */
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

        $config = Setup::createConfiguration((bool)DEBUG, null, null);
        $reader = new SimpleAnnotationReader();
        $reader->addNamespace('Doctrine\ORM\Mapping');
        $cachedReader = new CachedReader($reader, new ArrayCache());
        $annotationDriver = new DoctrineAnnotationDriver($cachedReader, (array)$paths);

        $config->setMetadataDriverImpl($annotationDriver);

        $entityManager = EntityManager::create($conn, $config);
        $this->di->set($entityManager, 'em');
    }

    /**
     * Add resolver extension
     */
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
