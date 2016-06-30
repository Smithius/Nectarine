<?php

namespace Di;

use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\FilesystemCache;
use phpDocumentor\Reflection\DocBlock;
use Annotation\Route;
use Annotation\Boot;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use ReflectionClass;
use ReflectionMethod;

class Parser
{
    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var \Doctrine\Common\Cache\FilesystemCache
     */
    protected $cache;

    /**
     * @var string
     */
    protected $type = 'prod';

    public function __construct()
    {
        AnnotationRegistry::registerLoader(function ($class) {
            return class_exists($class);
        });

        $this->annotationReader = new SimpleAnnotationReader();
        $this->annotationReader->addNamespace('Annotation');

        $this->cache = new FilesystemCache(CORE . '/cache/parser/' . $this->type);
    }

    /**
     * Load definition from cache or parse
     *
     * @param array $dirs
     * @return array
     */
    public function load(array $dirs)
    {
        $hash = md5(serialize($dirs));
        if ($this->cache->contains($hash)) {
            return $this->cache->fetch($hash);
        }

        $result = $this->parse($dirs);
        $this->cache->save($hash, $result);
        return $result;
    }

    /**
     * Parse annotations
     *
     * @param array $dirs
     * @return array
     */
    public function parse(array $dirs)
    {
        $result = array('tags' => array());
        foreach ($dirs as $dir) {
            $dirIt = new RecursiveDirectoryIterator($dir);
            $ite = new RecursiveIteratorIterator($dirIt);
            $files = new RegexIterator($ite, '/^.+\.php$/i', RegexIterator::GET_MATCH);

            foreach ($files as $file) {
                $fileName = current($file);
                $className = str_replace('/', '\\', substr($fileName, strlen($dir) + 1, -strlen('.php')));
                if (!isset($result['objects'][$className]) && class_exists($className)) {
                    $def = $this->parseClass($className);

                    $result['objects'][$className] = $def['object'];
                    $result['tags'] = array_merge_recursive($result['tags'], $def['tags']);
                }
            }
        }

        return $result;
    }

    /**
     * @param $className
     * @return array
     * @todo reorganize to object structure
     */
    protected function parseClass($className)
    {
        $classArray = array();
        $tags = array();

        $ref = new ReflectionClass($className);
        foreach ($this->annotationReader->getClassAnnotations($ref) as $annotation) {
            if ($annotation instanceof Boot) {
                $annotation->name = $ref->getName();
                $tags['boot'][] = $annotation;
            }
        }

        foreach ($ref->getProperties() as $property) {
            foreach ($this->annotationReader->getPropertyAnnotations($property) as $annotation) {
                if (!$annotation->getName()) {
                    $varTag = reset((new DocBlock($property))->getTagsByName('var'));
                    $annotation->name = $varTag ? $varTag->getType() : $property->name;
                }
                $classArray['properties'][$property->getName()] = $annotation;
            }
        }

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            foreach ($this->annotationReader->getMethodAnnotations($method) as $annotation) {
                $parameters = $this->parseParams($method);
                if ($annotation instanceof Route) {
                    $annotation->setName($ref->getName() . '::' . $method->getName());
                    $annotation->setParameters($parameters);
                    $tags['route'][] = $annotation;
                }

                $classArray['methods'][$method->getName()]['parameters'] = $parameters;
            }
        }

        $constructor = $ref->getConstructor();
        if ($constructor) {
            $classArray['methods'][$constructor->getName()]['parameters'] = $this->parseParams($constructor);
        }

        return array('object' => $classArray, 'tags' => $tags);
    }

    /**
     * Params parser
     *
     * @param \ReflectionMethod $method
     * @return array
     */
    private function parseParams($method)
    {
        $result = array();
        foreach ($method->getParameters() as $parameter) {
            $result[$parameter->name] = array(
                'required' => !$parameter->isDefaultValueAvailable()
            );

            if (!$result[$parameter->name]['required'])
                $result[$parameter->name]['default'] = $parameter->getDefaultValue();
            if ($parameter->getClass()) {
                $result[$parameter->name]['type'] = $parameter->getClass();
            }
        }

        $paramTags = (new DocBlock($method))->getTagsByName('param');
        if (is_array($paramTags))
            foreach ($paramTags as $paramTag) {
                $name = ltrim($paramTag->getVariableName(), '$');
                if (isset($result[$name]) && $paramTag->getType()) {
                    $result[$name]['type'] = $paramTag->getType();
                } else
                    @trigger_error("Invalid documentation for method ({$method->name}) in class ($method->class)", E_USER_WARNING);
            }

        return $result;
    }
}
