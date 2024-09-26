<?php
declare(strict_types=1);

namespace App\Helper;

use App\Exception\QueryException;
use Swoole\Coroutine\PostgreSQL;
use Hyperf\DB\DB;
use Hyperf\DB\Exception\RuntimeException;

class Database
{
    protected string $poolName;
    private int $transactionLevel = 0;

    public function __construct(string $poolName = 'default')
    {
        $this->poolName = $poolName;
    }

    public function run(string $query)
    {
        return $this->query($query);
    }

    /**
     * Executa uma query que busca por multiplos resultados.
     * @throws QueryException
     */
    public function query(string $query)
    {
        try {
            return DB::connection($this->poolName)->query($query);
        } catch (\PDOException $exception) {
            throw QueryException::failed($exception->getMessage(), $query, $exception);
        }
    }

    /**
     * Executa uma query que busca por um único resultado.
     * @throws QueryException
     */
    public function fetch(string $query)
    {
        try {
            return DB::connection($this->poolName)->fetch($query);
        } catch (\PDOException $exception) {
            throw QueryException::failed($exception->getMessage(), $query, $exception);
        }
    }

    /**
     * Executa uma query que retorna a quantidade de registros afetados.
     * @throws QueryException
     */
    public function execute(string $sql): int
    {
        try {
            return DB::connection($this->poolName)->execute($sql);
        } catch (\PDOException $exception) {
            throw QueryException::failed($exception->getMessage(), $sql, $exception);
        }
    }

    public function insert(string $table, array $values)
    {
        // CHECK IF IS A MULTI INSERT
        if(!empty($values[0])){
            $lines = [];
            foreach ($values as $line){
                $result = $this->insertFormat($line);
                $lines[] = $result["fieldValues"];
            }
            $valuesToInsert = implode(",", $lines);
        }else{
            $result = $this->insertFormat($values);
            $valuesToInsert = $result["fieldValues"];
        }

        $columns = ' (' . implode(", ", $result["fields"]) .  ') VALUES ';
        $query = "INSERT INTO $table $columns $valuesToInsert";

        return boolval($this->execute($query));
    }

    private function insertFormat(array $values): array
    {
        $fields = [];
        $fieldValues = [];

        foreach ($values as $key => $val) {
            $fields[] = $key;
            $fieldValues[] = is_string($val) ? "'" . str_replace("'", "\"", $val) . "'" : $val;
        }
        
        return ["fields" => $fields, "fieldValues" => '(' . implode(", ", $fieldValues) . ')'];
    }

    /**
     * Inicia a transação.
     */
    public function beginTransaction(): void
    {   
        if($this->transactionLevel > 0){
            $this->transactionLevel++;
            return;
        }
        DB::beginTransaction();
        $this->transactionLevel = 1;
    }

    /**
     * Efetiva a transação.
     * @throws \Hyperf\DB\Exception\RuntimeException
     */
    public function commit(): void
    {   
        if($this->transactionLevel > 1){
            $this->transactionLevel--;
            return;
        }
        if($this->transactionLevel === 0){
            throw new RuntimeException("Sem transações para efetivar");
        }
        DB::commit();
        $this->transactionLevel = 0;
    }

    /**
     * Desfaz a transação.
     * @throws \Hyperf\DB\Exception\RuntimeException
     */
    public function rollBack(): void
    {   
        if($this->transactionLevel > 1){
            $this->transactionLevel--;
            return;
        }
        if($this->transactionLevel === 0){
            throw new RuntimeException("Sem transações para desfazer");
        }
        DB::rollBack();
        $this->transactionLevel = 0;
    }

}

