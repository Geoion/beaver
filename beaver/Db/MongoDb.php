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
 * A mongodb driver.
 *
 * @author You Ming
 *
 * [Options]
 *  debug           : Debug mode.
 *  servers         : An array of servers configurations.
 *  server          : A server configurations.
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
        if (isset($options['server'])) {
            $options['servers'] = isset($options['servers']) ? array_merge($options['servers'], $options['server'])
                : [$options['server']];
        }

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
     *
     * [Options]
     *  hint (string|document): The index to use. If a document, it will be interpretted as
     *      an index specification and a name will be generated.
     *  limit (integer): The maximum number of documents to count.
     *  maxTimeMS (integer): The maximum amount of time to allow the query to run.
     *  readConcern (MongoDB\Driver\ReadConcern): Read concern. [require MongoDb >= 3.2]
     *  readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *  skip (integer): The number of documents to skip before returning the documents.
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
     *
     * [Options]
     *  bypassDocumentValidation (boolean): If true, allows the write to opt out of document
     *      level validation.
     *  writeConcern (MongoDB\Driver\WriteConcern): Write concern.
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
            $id = is_array($data) ? $data['_id'] : $data->_id;
        }

        return $result->getInsertedCount() > 0 ? $id : false;
    }

    /**
     * @inheritdoc
     *
     * [Options]
     *  bypassDocumentValidation (boolean): If true, allows the write to opt out of document
     *      level validation.
     *  ordered (boolean): If true, when an insert fails, return without performing the
     *      remaining writes. If false, when a write fails, continue with the remaining
     *      writes, if any. The default is true.
     *  writeConcern (MongoDB\Driver\WriteConcern): Write concern.
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
                    $ids[$i] = is_array($document) ? $document['_id'] : $data->_id;
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
     *
     * [Options]
     *  comment (string): Attaches a comment to the query. If "$comment" also exists in the
     *      modifiers document, this option will take precedence.
     *  maxTimeMS (integer): The maximum amount of time to allow the query to run. If "$maxTimeMS"
     *      also exists in the modifiers document, this option will take precedence.
     *  modifiers (document): Meta-operators modifying the output or behavior of a query.
     *  projection (document): Limits the fields to return for the matching document.
     *  readConcern (MongoDB\Driver\ReadConcern): Read concern. [require MongoDb >= 3.2]
     *  readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *  skip (integer): The number of documents to skip before returning.
     *  sort (document): The order in which to return matching documents. If "$orderby" also
     *      exists in the modifiers document, this option will take precedence.
     */
    public function findOne($table, $where, $fields = [], $options = [])
    {
        $options['limit'] = 1; // Finds one.

        $documents = $this->findMany($table, $where, $fields, $options);

        return $documents ? current($documents) : null;
    }

    /**
     * @inheritdoc
     *
     * [Options]
     *  allowPartialResults (boolean): Get partial results from a mongos if some shards are
     *      inaccessible (instead of throwing an error).
     *  batchSize (integer): The number of documents to return per batch.
     *  comment (string): Attaches a comment to the query. If "$comment" also exists in the
     *      modifiers document, this option will take precedence.
     *  cursorType (enum): Indicates the type of cursor to use. Must be either NON_TAILABLE,
     *      TAILABLE, or TAILABLE_AWAIT. The default is NON_TAILABLE.
     *  limit (integer): The maximum number of documents to return.
     *  maxTimeMS (integer): The maximum amount of time to allow the query to run. If "$maxTimeMS"
     *      also exists in the modifiers document, this option will take precedence.
     *  modifiers (document): Meta-operators modifying the output or behavior of a query.
     *  noCursorTimeout (boolean): The server normally times out idle cursors after an inactivity
     *      period (10 minutes) to prevent excess memory use. Set this option to prevent that.
     *  oplogReplay (boolean): Internal replication use only. The driver should not set this.
     *  projection (document): Limits the fields to return for the matching document.
     *  readConcern (MongoDB\Driver\ReadConcern): Read concern. [require MongoDb >= 3.2]
     *  readPreference (MongoDB\Driver\ReadPreference): Read preference.
     *  skip (integer): The number of documents to skip before returning.
     *  sort (document): The order in which to return matching documents. If "$orderby" also
     *      exists in the modifiers document, this option will take precedence.
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
     *
     * [Options]
     *  bypassDocumentValidation (boolean): If true, allows the write to opt out of document
     *      level validation.
     *  upsert (boolean): When true, a new document is created if no document matches the query.
     *      The default is false.
     *  writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     */
    public function updateOne($table, $where, $data, $options = [])
    {
        $options['multi'] = false;

        return $this->updateMany($table, $where, $data, $options);
    }

    /**
     * @inheritdoc
     *
     * [Options]
     *  bypassDocumentValidation (boolean): If true, allows the write to opt out of document
     *      level validation.
     *  upsert (boolean): When true, a new document is created if no document matches the query.
     *      The default is false.
     *  writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     */
    public function updateMany($table, $where, $data, $options = [])
    {
        $namespace = $this->getNamespace($table);
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;
        $options['upsert'] = false;
        $options['multi'] = isset($options['multi']) ? $options['multi'] : true;

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
     *
     * [Options]
     *  writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     */
    public function deleteOne($table, $where, $options = [])
    {
        $options['limit'] = 1;

        return $this->deleteMany($table, $where, $options);
    }

    /**
     * @inheritdoc
     *
     * [Options]
     *  writeConcern (MongoDB\Driver\WriteConcern): Write concern.
     */
    public function deleteMany($table, $where, $options = [])
    {
        $namespace = $this->getNamespace($table);
        $writeConcern = isset($options['writeConcern']) ? $options['writeConcern'] : $this->writeConcern;
        $deleteOptions = isset($options['limit']) ? ($options['limit'] ? 1 : 0) : 0;

        try {
            $bulk = new BulkWrite($options);
            $bulk->delete($where, $deleteOptions);

            $server = $this->selectServer($options);
            $result = $server->executeBulkWrite($namespace, $bulk, $writeConcern);
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        return false !== $result ? $result->getDeletedCount() : false;
    }

    /**
     * Finds a document and modify it.
     *
     * [Options]
     *  bypassDocumentValidation (boolean): If true, allows the write to opt out of document
     *      level validation.
     *  fields (document): Limits the fields to return for the matching document.
     *  maxTimeMS (integer): The maximum amount of time to allow the query to run.
     *  new (boolean): When true, returns the modified document rather than the original.
     *      This option is ignored for remove operations. The default is false.
     *  remove (boolean): When true, removes the matched document. This option cannot be true
     *      if the update option is set. The default is false.
     *  sort (document): Determines which document the operation modifies if the query selects
     *      multiple documents.
     *  upsert (boolean): When true, a new document is created if no document matches the query.
     *      This option is ignored for remove operations. The default is false.
     *  writeConcern (MongoDB\Driver\WriteConcern): Write concern. [require MongoDb >= 3.2]
     *
     * @param string $table
     * @param array $where
     * @param array $update
     * @param array $options
     * @return mixed
     */
    public function findAndModify($table, $where, $update = [], $options = [])
    {
        $options['query'] = $where;

        if (isset($options['remove']) && $options['remove']) {
            unset($options['update'], $options['new'], $options['upsert']);
        } else {
            $options['update'] = $update;
        }

        try {
            $command = ['findAndModify' => $table];
            if (!empty($where)) {
                $command['query'] = $where;
            }
            $command = $this->applyOptionsForCommand($command, $options, ['fields', 'query', 'sort', 'update', 'new',
                'upsert', 'remove', 'maxTimeMS', 'bypassDocumentValidation', 'writeConcern']);
            $command = new Command($command);

            $server = $this->selectServer($options);
            $cursor = $server->executeCommand($this->db, $command);
            $cursor->setTypeMap(self::$typeMap);
            $result = $cursor->toArray();
        } catch (Exception $e) {
            $this->saveError($e);
            return false;
        }

        if (!isset($result['value'])) {
            return null;
        }

        if ((isset($options['upsert']) && $options['upsert']) && (!isset($options['new']) || !$options['new'])
                && isset($result['lastErrorObject']['updatedExisting'])
                && !$result['lastErrorObject']['updatedExisting']) {
            return null;
        }

        return $result['value'];
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