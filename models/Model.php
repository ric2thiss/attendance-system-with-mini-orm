<?php
require_once __DIR__ . '/../query/QueryBuilder.php';

// class Model extends QueryBuilder {
//     protected $table;

//     protected static $connection; 

//     public function __construct(PDO $dbconn) {
//         parent::__construct($dbconn);
//         static::$connection = $dbconn;
//     }

//     public static function tableName() {
//         return (new static(static::$connection))->table;
//     }

//     public static function query() {
//         return new static(static::$connection);
//     }

//     public static function all() {
//         return static::query()->table(static::tableName())->get();
//     }

//     public static function find($id) {
//         return static::query()->table(static::tableName())->where("id", $id)->first();
//     }
// }

// class Model extends QueryBuilder {
//     protected $table;               // set this in each child model
//     protected static $pdo = null;   // shared PDO for all models

//     public function __construct(?PDO $dbconn = null) {
//         if ($dbconn instanceof PDO) {
//             static::$pdo = $dbconn;
//             parent::__construct($dbconn);
//             return;
//         }

//         if (!static::$pdo) {
//             throw new Exception("No PDO connection set. Call Model::setConnection(\$pdo) first.");
//         }

//         parent::__construct(static::$pdo);
//     }


//     // One-time bootstrap from your app (e.g., in bootstrap.php)
//     public static function setConnection(PDO $pdo): void {
//         static::$pdo = $pdo;
//     }

//     // Returns the table name defined by the child model
//     public static function tableName(): string {
//         $instance = new static(static::$pdo);
//         return $instance->table;
//     }

//     // Start a new query builder for this model
//     public static function query(): self {
//         return (new static(static::$pdo))->table(static::tableName());
//     }

//     // Like Laravel: immediately fetch all rows (array)
//     public static function all(): array {
//         return static::query()->get();
//     }

//     // Find one row by id (or null)
//     public static function find($id) {
//         return static::query()->where("id", $id)->first();
//     }
// }

// class Model extends QueryBuilder {
//     protected static $db;
//     protected $table;
//     protected $fillable = []; // 👈 default empty

//     public function __construct(PDO $dbconn) {
//         parent::__construct($dbconn);
//         static::$db = $dbconn;
//     }

//     public static function setConnection(PDO $dbconn) {
//         static::$db = $dbconn;
//     }

//     public static function query() {
//         return new static(static::$db);
//     }

//     public static function all() {
//         return static::query()->table((new static)->table)->get();
//     }

//     public static function find($id) {
//         return static::query()->table((new static)->table)->where("id", $id)->first();
//     }

//     public static function create(array $data) {
//         $instance = new static(static::$db);

//         // Only keep fillable keys
//         $filtered = array_intersect_key($data, array_flip($instance->fillable));

//         return static::query()->table($instance->table)->insert($filtered);
//     }
// }

class Model extends QueryBuilder {
    protected $table;                 // child models must define this
    protected $fillable = [];         // define fillable columns in child model
    protected static $pdo = null;     // shared PDO across all models

    public function __construct(?PDO $dbconn = null) {
        if ($dbconn instanceof PDO) {
            static::$pdo = $dbconn;
            parent::__construct($dbconn);
            return;
        }

        if (!static::$pdo) {
            throw new Exception("No PDO connection set. Call Model::setConnection(\$pdo) first.");
        }

        parent::__construct(static::$pdo);
    }

    // One-time bootstrap from your app (e.g., in bootstrap.php)
    public static function setConnection(PDO $pdo): void {
        static::$pdo = $pdo;
    }

    // Returns the table name defined by the child model
    public static function tableName(): string {
        return (new static(static::$pdo))->table;
    }

    // Start a new query builder for this model
    public static function query(): self {
        return (new static(static::$pdo))->table(static::tableName());
    }

    // Fetch all rows
    public static function all(): array {
        return static::query()->get();
    }

    // Find one row by id (or null)
    public static function find($id) {
        return static::query()->where("id", $id)->first();
    }

    // Insert new record with fillable protection
    public static function create(array $data) {
        $instance = new static(static::$pdo);

        // filter only fillable keys
        $filtered = array_intersect_key($data, array_flip($instance->fillable));

        if (empty($filtered)) {
            throw new Exception("No valid fillable fields provided for insert.");
        }

        return static::query()->insert($filtered);
    }
}


