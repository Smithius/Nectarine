<?php

namespace Di;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class DebugParser extends Parser
{
    /**
     * @var string
     */
    protected $type = 'debug';

    /**
     * Load definiton from cache or parse
     * @param array $dirs
     * @return array
     */
    public function load(array $dirs)
    {
        $resp = array();
        foreach ($dirs as $dir) {
            $dirIt = new RecursiveDirectoryIterator($dir);
            $ite = new RecursiveIteratorIterator($dirIt);
            $files = new RegexIterator($ite, '/^.+\.php$/i', RegexIterator::GET_MATCH);

            $resp = array_merge_recursive($this->loadDir($files, $dir), $resp);
        }

        return $resp;
    }

    /**
     * Load cache for directory or parse
     * @param $files
     * @param $dir
     * @return array
     */
    private function loadDir($files, $dir)
    {
        $hash = md5($dir);
        $mtime = filemtime(__FILE__);
        foreach ($files as $file) {
            $filePath = current($file);
            $mtime = max($mtime, filemtime($filePath));
        }

        if ($this->cache->contains($hash)) {
            $cache = $this->cache->fetch($hash);
            if ($cache['mtime'] >= $mtime) {
                return $cache;
            }
        }

        $result = $this->parseFiles($files, strlen($dir));
        $result['mtime'] = $mtime;

        $this->cache->save($hash, $result);
        return $result;
    }

    /**
     * Parse annotations
     * @param array $files
     * @param int $dirlen
     * @return array
     */
    public function parseFiles($files, $dirlen)
    {
        $result = array('tags' => array());
        foreach ($files as $file) {
            $fileName = current($file);
            $className = str_replace('/', '\\', substr($fileName, $dirlen + 1, -strlen('.php')));
            if (class_exists($className)) {
                $def = $this->parseClass($className);
                $result['objects'][$className] = $def['object'];
                $result['tags'] = array_merge_recursive($result['tags'], $def['tags']);
            }
        }

        return $result;
    }

}