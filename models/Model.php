<?php
require_once __DIR__ . '/../query/QueryBuilder.php';

class Model extends QueryBuilder {
    protected $table;

    protected static $connection; // store PDO once for all models

    public function __construct(PDO $dbconn) {
        parent::__construct($dbconn);
        static::$connection = $dbconn;
    }

    public static function tableName() {
        return (new static(static::$connection))->table;
    }

    public static function query() {
        return new static(static::$connection);
    }

    public static function all() {
        return static::query()->table(static::tableName())->get();
    }

    public static function find($id) {
        return static::query()->table(static::tableName())->where("id", $id)->first();
    }
}
