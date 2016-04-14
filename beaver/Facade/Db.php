<?php
/**
 * Beaver - A framework for PHP
 *
 * @copyright (c) 2016 You Ming
 * @license The MIT License
 * @link http://beaver.funcuter.com
 */

namespace Beaver\Facade;

use Beaver\Facade;

/**
 * A facade of Db.
 *
 * @method static bool connect(array $options = [])
 * @method static void close()
 * @method static mixed selectDb($db, $options = [])
 * @method static mixed count($table, $where = [], $options = [])
 * @method static mixed insertOne($table, $data, $options = [])
 * @method static mixed insertMany($table, array $data, $options = [])
 * @method static mixed findOne($table, $where, $fields = [], $options = [])
 * @method static mixed findMany($table, $where, $fields = [], $options = [])
 * @method static mixed updateOne($table, $where, $data, $options = [])
 * @method static mixed updateMany($table, $where, $data, $options = [])
 * @method static mixed deleteOne($table, $where, $options = [])
 * @method static mixed deleteMany($table, $where, $options = [])
 * @method static string getError();
 *
 * @author You Ming
 */
final class Db extends Facade
{
    /**
     * @inheritdoc
     */
    protected static function getAccessor()
    {
        return 'db';
    }
}
