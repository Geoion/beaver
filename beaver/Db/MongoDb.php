<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Db;

use Beaver\Db;
use Beaver\Exception\DbException;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Server;
use MongoDB\Driver\WriteConcern;

/**
 * A mongodb driver. Needs mongodb extension.
 *
 * @author You Ming
 *
 * [Options]
 *  debug           : Debug mode.
 *  servers         : An array of servers configurations.
 *  username        : The username for auth.
 *  password        : The password for auth.
 *  authDb          : The database name for auth.
 *  db              : The default database name.
 */
class MongoDb extends Db
{
    /**
     * Map of types.
     *
     * @var array
     */
    protected static $typeMap = [
        'root' => 'array',
        'array' => 'array',
        'document' => 'array'
    ];

    /**
     * A handler to mongodb.
     *
     * @var Manager
     */
    protected $manager;

    /**
     * Current database name.
     *
     * @var string
     */
    protected $db;

    /**
     * Read preference.
     *
     * @var ReadPreference
     */
    protected $readPreference;

    /**
     * Write concern.
     *
     * @var WriteConcern
     */
    protected $writeConcern;

    /**
     * Builds an uri for connecting.
     *
     * @param array $options
     * @return string
     */
    protected function parseUri($options)
    {
        $servers = [];
        if (isset($options['servers']) && $options['servers']) {
            foreach ($options['servers'] as $server) {
                if (is_array($server)) {
                    $servers[] = $server[0] . ':' . $server[1];
                } else {
                    $servers[] = $server;
                }
            }
        } else {
            throw new DbException('At least one server must be provided.');
        }

        $auth = isset($options['username']) ? $options['username'] . ':' . $options['password'] . '@' : '';
        $db = isset($options['authDb']) ? '/' . $options['authDb'] : isset($options['db']) ? '/' . $options['db'] : '';

        return 'mongodb://' . $auth . implode(',', $servers) . $db;
    }

    /**
     * Builds an options for connecting.
     *
     * @param array $options
     * @return array
     */
    protected function parseUriOptions($options)
    {
        $availableOptions = [
            // Replica Set Option
            'replicaSet',
            // Connection Options
            'ssl', 'connectTimeoutMS', 'socketTimeoutMS',
            // Connection Pool Options
            'maxPoolSize', 'minPoolSize', 'maxIdleTimeMS', 'waitQueueMultiple', 'waitQueueTimeoutMS',
            // Write Concern Options
            'w', 'wtimeoutMS', 'journal',
            // Read Concern Options
            'readConcernLevel',
            // Read Preference Options
            'readPreference', 'readPreferenceTags',
            // Authentication Options
            'authSource', 'authMechanism', 'gssapiServiceName',
            // Miscellaneous Configuration
            'uuidRepresentation',
        ];

        $uriOptions = [];
        foreach ($availableOptions as $key) {
            if (isset($options[$key])) {
                $uriOptions[$key] = $options[$key];
            }
        }

        return $uriOptions;
    }

    /**
     * Parses options.
     *
     * @param array $options
     */
    protected function parseOptions($options)
    {
        $this->readPreference = isset($options['readPreference']) ? $options['readPreference']
            : new ReadPreference(ReadPreference::RP_PRIMARY);
        $this->writeConcern = isset($options['writeConcern']) ? $options['writeConcern']
            : new WriteConcern(WriteConcern::MAJORITY);
    }

    /**
     * @inheritdoc
     */
    public function connect(array $options = [])
    {
        if (isset($options['debug'])) {
            $this->setDebug($options['debug']);
        }

        $uri = $this->parseUri($options);
        $uriOptions = $this->parseUriOptions($options);

        $this->parseOptions($options);

        try {
            $this->manager = new Manager($uri, $uriOptions);
            if (isset($options['db'])) {
                $this->selectDb($options['db']);
            }

            return true;
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }
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
    public function selectDb($db, $options = [])
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function count($table, $where = [], $options = [])
    {
        $readPreference = isset($options['readPreference']) ? $options['readPreference'] : $this->readPreference;

        try {
            $command = ['count' => $table];
            if (!empty($where)) {
                $command['query'] = $where;
            }
            $command = $this->applyOptionsForCommand($command, $options, ['hint', 'limit', 'maxTimeMS', 'skip']);
            $command = new Command($command);

            $server = $this->selectServer($options);
            $cursor = $server->executeCommand($this->db, $command, $readPreference);
            $cursor->setTypeMap(self::$typeMap);
            $result = current($cursor->toArray());
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        if (!isset($result['n']) || !(is_integer($result['n']) || is_float($result['n']))) {
            $this->saveError("Mongodb's command [count] did not return a numeric 'n' value");
            return false;
        }

        return (int) $result['n'];
    }

    /**
     * @inheritdoc
     */
    public function insertOne($table, $data, $options = [])
    {
        $namespace = $this->getNamespace($table);
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;

        try {
            $bulk = new BulkWrite($options);
            $id = $bulk->insert($data);

            $server = $this->selectServer($options);
            $result = $server->executeBulkWrite($namespace, $bulk, $writeConcern);
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        if (null === $id) {
            $id = is_array($data['_id']) ? $data['_id'] : $data->_id;
        }

        return $result->getInsertedCount() > 0 ? $id : false;
    }

    /**
     * @inheritdoc
     */
    public function insertMany($table, array $data, $options = [])
    {
        $namespace = $this->getNamespace($table);
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;

        $ids = [];
        try {
            $bulk = new BulkWrite($options);
            foreach ($data as $i => $document) {
                $id = $bulk->insert($document);
                if (null !== $id) {
                    $ids[$i] = $id;
                } else {
                    $ids[$i] = is_array($data['_id']) ? $data['_id'] : $data->_id;
                }
            }

            $server = $this->selectServer($options);
            $result = $server->executeBulkWrite($namespace, $bulk, $writeConcern);
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        return $result->getInsertedCount() > 0 ? $ids : false;
    }

    /**
     * @inheritdoc
     */
    public function findOne($table, $where, $fields = [], $options = [])
    {
        $options['limit'] = 1; // Finds one.

        $documents = $this->findMany($table, $where, $fields, $options);

        return $documents ? current($documents) : null;
    }

    /**
     * @inheritdoc
     */
    public function findMany($table, $where, $fields = [], $options = [])
    {
        $namespace = $this->getNamespace($table);
        $readPreference = isset($options['readPreference']) ? $options['readPreference'] : $this->readPreference;
        $options['projection'] = $fields;

        try {
            $query = new Query($where, $options);

            $server = $this->selectServer($options);
            $cursor = $server->executeQuery($namespace, $query, $readPreference);
            $cursor->setTypeMap(self::$typeMap);
            $documents = $cursor->toArray();
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        return false !== $documents ? $documents : [];
    }

    /**
     * @inheritdoc
     */
    public function updateOne($table, $where, $data, $options = [])
    {
        $options['multi'] = false;

        return $this->updateMany($table, $where, $data, $options);
    }

    /**
     * @inheritdoc
     */
    public function updateMany($table, $where, $data, $options = [])
    {
        $namespace = $this->getNamespace($table);
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;

        try {
            $bulk = new BulkWrite($options);
            $bulk->update($where, $data, $options);

            $server = $this->selectServer($options);
            $result = $server->executeBulkWrite($namespace, $bulk, $writeConcern);
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        return false !== $result ? $result->getModifiedCount() : false;
    }

    /**
     * @inheritdoc
     */
    public function deleteOne($table, $where, $option = [])
    {
        $options['justOne'] = false;

        return $this->deleteMany($table, $where, $options);
    }

    /**
     * @inheritdoc
     */
    public function deleteMany($table, $where, $option = [])
    {
        $namespace = $this->getNamespace($table);
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;
        $options['limit'] = isset($options['justOne']) ? ($options['justOne'] ? 1 : 0) : 0;

        try {
            $bulk = new BulkWrite($options);
            $bulk->delete($where, $options);

            $server = $this->selectServer($options);
            $result = $server->executeBulkWrite($namespace, $bulk, $writeConcern);
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        return false !== $result ? $result->getDeletedCount() : false;
    }

    /**
     * Applies options for a command.
     *
     * @param array $command
     * @param array $options
     * @param array $optionNames
     * @return array
     */
    protected function applyOptionsForCommand($command, $options, $optionNames)
    {
        foreach ($optionNames as $name) {
            if (isset($options[$name])) {
                $command[$name] = $options[$name];
            }
        }

        return $command;
    }

    /**
     * Gets the collection namespace.
     *
     * @param string $collection
     * @return string
     */
    protected function getNamespace($collection)
    {
        return $this->db . '.' . $collection;
    }

    /**
     * Selects a server for operating.
     *
     * @param array $options
     * @return Server
     */
    protected function selectServer($options)
    {
        $readPreference = isset($options['readPreference']) ? $options['readPreference'] : $this->readPreference;

        return $this->manager->selectServer($readPreference);
    }

    /**
     * Gets all databases' info. Using this method need an auth for admin database.
     *
     * @return array
     */
    public function listDbs()
    {
        $command = ['listDatabases' => 1];

        try {
            $cursor = $this->manager->executeCommand('admin', new Command($command));
            $cursor->setTypeMap(self::$typeMap);
            $result = current($cursor->toArray());
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        if (!isset($result['databases']) || !is_array($result['databases'])) {
            $this->saveError("Mongodb's command [listDatabases]  did not return a 'databases' array");
            return false;
        }

        return $result['databases'];
    }
}