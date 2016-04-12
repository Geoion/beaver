<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Storage;

use Beaver\Exception\StorageException;
use Beaver\Storage;

/**
 * A storage that saving data in file system.
 *
 * @author You Ming
 *
 * [Options]
 *  directory   : The directory for storage.
 */
class FileStorage extends Storage
{
    /**
     * The directory for storage.
     *
     * @var string
     */
    protected $directory;

    /**
     * Gets file path for a given name.
     *
     * @param string $bucket The name of bucket.
     * @param string $name
     * @return string
     */
    private function getFilePath($bucket, $name)
    {
        return $this->directory . $bucket . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], '_', $name);
    }

    /**
     * @inheritdoc
     */
    public function connect(array $options = [])
    {
        $this->directory = isset($options['directory']) ? $options['directory'] : $this->context->getStorageDir();

        if (!is_dir($this->directory)) {
            if (!mkdir($this->directory, 0755, true)) {
                $dirName = dirname($this->directory);
                throw new StorageException("Storage directory [$dirName] can not be written.");
            }
        }

        if (!is_writable($this->directory)) {
            throw new StorageException("Storage directory [{$this->directory}] can not be written.");
        }

        $this->directory = realpath($this->directory) . DIRECTORY_SEPARATOR;

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
    public function listBuckets()
    {
        $files = scandir($this->directory);
        if (false === $files) {
            return false;
        }

        $buckets = [];
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && is_dir($this->directory . $file)) {
                $buckets[] = $file;
            }
        }

        return $buckets;
    }

    /**
     * @inheritdoc
     */
    public function listItems($bucket, $skip = 0, $limit = 0)
    {
        $dir = $this->directory . $bucket . DIRECTORY_SEPARATOR;

        if (!is_dir($dir) || !is_readable($dir)) {
            return false;
        }

        if ($handler = opendir($dir)) {
            $i = 0;
            $items = [];

            while (false !== ($file = readdir($handler))) {
                if (is_file($dir . $file)) {
                    $i ++;
                    if ($i > $skip) {
                        $items[] = $file;

                        if ($limit > 0 && $skip + $limit - 1 < $i) {
                            break;
                        }
                    }
                }
            }

            closedir($handler);

            return $items;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function info($bucket, $name)
    {
        $filePath = $this->getFilePath($bucket, $name);

        if (is_file($filePath) && is_readable($filePath)) {
            $info = [
                'modify_time' => filemtime($filePath),
                'inode_time' => filectime($filePath),
                'access_time' => fileatime($filePath),
                'size' => filesize($filePath)
            ];

            return $info;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function read($bucket, $name)
    {
        $filePath = $this->getFilePath($bucket, $name);

        return file_get_contents($filePath);
    }

    /**
     * @inheritdoc
     */
    public function write($bucket, $name, $data)
    {
        $filePath = $this->getFilePath($bucket, $name);
        $dirName = dirname($filePath);

        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0755, true)) {
                return false;
            }
        }

        return file_put_contents($filePath, $data);
    }

    /**
     * @inheritdoc
     */
    public function append($bucket, $name, $data)
    {
        $content = $this->read($bucket, $name);
        if ($content) {
            $data = $content . $data;
        }

        return $this->write($bucket, $name, $data);
    }

    /**
     * @inheritdoc
     */
    public function exist($bucket, $name)
    {
        $filePath = $this->getFilePath($bucket, $name);

        return is_file($filePath);
    }

    /**
     * @inheritdoc
     */
    public function delete($bucket, $name)
    {
        $filePath = $this->getFilePath($bucket, $name);

        return is_file($filePath) ? unlink($filePath) : false;
    }

    /**
     * @inheritdoc
     */
    public function flush($bucket)
    {
        $dir = $this->directory . $bucket . DIRECTORY_SEPARATOR;
        $items = $this->listItems($bucket);

        foreach ($items as $item) {
            if (false === unlink($dir . $item)) {
                return false;
            }
        }

        return true;
    }
}