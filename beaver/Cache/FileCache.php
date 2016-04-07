<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Cache;

use Beaver\Cache;
use Beaver\Exception\CacheException;

/**
 * A cache that saving data in file system.
 *
 * @author You Ming
 *
 * [Options]
 *  directory   : The directory for storage.
 *  serializer  : Type of serializer.
 *  prefix      : Prefix for all key.
 *  expiry      : Default expiry time.
 *  compressor  : Type of compressor.
 *  check       : Uses data checking.
 */
class FileCache extends Cache
{
    /**
     * Magic for cache file header.
     *
     * @var string
     */
    protected static $magicMark = '0x160405';

    /**
     * Length of magic.
     *
     * @var int
     */
    protected static $magicMarkLength = 8;

    /**
     * The directory for storage.
     *
     * @var string
     */
    protected $directory;

    /**
     * Type of serializer.
     *
     * @var string
     */
    protected $serializer;

    /**
     * Prefix for all key.
     *
     * @var string
     */
    protected $prefix;

    /**
     * A default expiry time.
     *
     * @var int
     */
    protected $expiry = 0;

    /**
     * Type of compressor.
     *
     * @var bool
     */
    protected $compressor;

    /**
     * Uses data checking.
     *
     * @var bool
     */
    protected $check;

    /**
     * Gets cache file path for a given name.
     *
     * @param string $name
     * @return string
     */
    private function getFilePath($name) {
        return $this->directory . $this->prefix . md5($name) . '.tmp';
    }

    /**
     * @inheritdoc
     */
    public function connect(array $options = [])
    {
        $this->directory = isset($options['directory']) ? $options['directory'] : $this->context->getCacheDir();

        $this->serializer = isset($options['serializer']) ? $options['serializer'] : 'php';
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : '';
        $this->expiry = isset($options['expiry']) ? $options['expiry'] : 0;
        $this->compressor = isset($options['compressor']) ? $options['compressor'] : 'none';
        $this->check = isset($options['check']) ? $options['check'] : false;

        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0755, true)) {
                $dirName = dirname($this->directory);
                throw new CacheException("Cache directory [$dirName] can not be written.");
            }
        }

        if (!is_writable($this->directory)) {
            throw new CacheException("Cache directory [{$this->directory}] can not be written.");
        }

        $this->directory = realpath($this->directory) . DS;
        
        return true;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
    }

    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        $filePath = $this->getFilePath($name);
        if (!is_file($filePath)) {
            return $default;
        }

        $content = file_get_contents($filePath);
        if (false === $content) {
            return $default;
        }

        $startMagic = substr($content, 0, static::$magicMarkLength);
        $endMagic = substr($content, - static::$magicMarkLength);

        if ($startMagic !== static::$magicMark || $endMagic !== static::$magicMark) {
            return $default;
        }

        $expiry = (int) substr($content, static::$magicMarkLength, 10);
        if ($expiry != 0 && time() > $expiry) {
            // Out of expiry.
            unlink($filePath);
            return $default;
        }

        if ($this->check) {
            $check = substr($content, static::$magicMarkLength + 10, 32);
            $data = substr($content, static::$magicMarkLength + 42, - static::$magicMarkLength);
            if ($check != md5($data)) {
                // Checks failed.
                unlink($filePath);
                return $default;
            }
        } else {
            $data = substr($content, static::$magicMarkLength + 10, - static::$magicMarkLength);
        }

        $data = $this->uncompress($data);
        $data = $this->unserialize($data);

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function set($name, $value, $expiry = null)
    {
        if (null === $expiry) {
            $expiry = $this->expiry;
        }

        $filePath = $this->getFilePath($name);
        $data = $this->serialize($value);
        $data = $this->compress($data);
        $check = $this->check ? md5($data) : '';
        $expiry = $expiry ? time() + $expiry : 0;
        $data = static::$magicMark . sprintf('%010d', $expiry) . $check . $data . static::$magicMark;

        $result = file_put_contents($filePath, $data);

        return $result ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function exist($name)
    {
        return $this->get($name) !== null;
    }

    /**
     * @inheritdoc
     */
    public function delete($name)
    {
        return unlink($this->getFilePath($name));
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $files = scandir($this->directory);
        if ($files) {
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && is_dir($this->directory . $file)) {
                    array_map('unlink', glob($this->directory . $file . '/*.*' ) );
                } elseif (is_file($this->directory . $file)) {
                    unlink($this->directory . $file);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Serializes data.
     *
     * @param mixed $data
     * @return string
     */
    protected function serialize($data)
    {
        switch ($this->serializer) {
            case 'json':
                return json_encode($data);
            default:
                return serialize($data);
        }
    }

    /**
     * Unserializes data.
     *
     * @param string $data
     * @return mixed
     */
    protected function unserialize($data)
    {
        switch ($this->serializer) {
            case 'json':
                return json_decode($data, true);
            default:
                return unserialize($data);
        }
    }

    /**
     * Compresses data.
     *
     * @param string $data
     * @return string
     */
    protected function compress($data)
    {
        switch ($this->compressor) {
            case 'gzip':
                return gzcompress($data);
            default:
                return $data;
        }
    }

    /**
     * Uncompresses data.
     *
     * @param string $data
     * @return string
     */
    protected function uncompress($data)
    {
        switch ($this->compressor) {
            case 'gzip':
                return gzuncompress($data);
            default:
                return $data;
        }
    }
}