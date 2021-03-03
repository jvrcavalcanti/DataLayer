<?php

namespace Accolon\Izanami;

use Accolon\Izanami\Exceptions\DBConfigException;
use Accolon\Izanami\Exceptions\TransactionException;
use Accolon\Izanami\Model;
use Closure;
use PDO;
use PDOException;

class DB
{
    protected static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (!self::$instance) {
            if (!defined('DB_CONFIG')) {
                throw new DBConfigException("const 'DB_CONFIG' no defined");
            }
    
            $config = DB_CONFIG;
    
            $url = $config['driver'] . ":";
            $url .= "host=" . $config['host'] . ";";
            $url .= "port=" . $config['port'] . ";";
            $url .= "charset=" . $config['charset'] . ";";
            $url .= "dbname=" . $config['name'];

            if ($config['driver'] === "sqlite") {
                $url = "sqlite:" . $config['name'] . ".db";
                self::$instance = new PDO($url);
            } else {
                self::$instance = new PDO(
                    $url,
                    $config['user'],
                    $config['password']
                );
            }
            
                
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        }

        return self::$instance;
    }

    public static function setConnection(PDO $pdo)
    {
        self::$instance = $pdo;
    }

    public static function table(string $tableName, $attributes = []): Model
    {
        return (new class extends Model {
            protected string $table;
        })->setTable($tableName);
    }

    public static function raw($sql, $params = [])
    {
        $stmt = self::connection()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function selectRaw($sql, $params = [])
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function transaction(Closure $callback)
    {
        try {
            Db::beginTransaction();
            $callback();
            Db::commit();
        } catch (PDOException $e) {
            Db::rollBack();
            throw new TransactionException("Transaction roll back");
        }
    }

    public static function beginTransaction()
    {
        Db::connection()->beginTransaction();
    }

    public static function commit()
    {
        Db::connection()->commit();
    }

    public static function rollBack()
    {
        Db::connection()->rollBack();
    }
}
