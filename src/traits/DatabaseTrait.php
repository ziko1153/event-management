<?php

namespace App\Traits;

use App\Exception\DatabaseException;
use Config\Database;
use PDO;

trait DatabaseTrait
{
    protected string $table;

    private function withConnection(callable $callback)
    {
        $this->ensureTableIsSet();
        $db = Database::getInstance();
        $connection = $db->getConnection();

        try {
            return $callback($connection);
        } catch (\PDOException $e) {
            throw new DatabaseException("Database error in table '{$this->table}': " . $e->getMessage(), (int) $e->getCode(), $e);
        } finally {
            $db->releaseConnection($connection);
        }
    }

    public function findById(int $id): ?array
    {
        return $this->withConnection(function (PDO $connection) use ($id) {
            $stmt = $connection->prepare("SELECT * FROM {$this->table} WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch() ?: null;
        });
    }

    public function findByColumn(string $column, string $value): ?array
    {
        return $this->withConnection(function (PDO $connection) use ($column, $value) {
            $stmt = $connection->prepare("SELECT * FROM {$this->table} WHERE {$column} = :{$column}");
            $stmt->execute([":{$column}" => $value]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        });
    }

    public function findAll(array $criteria = []): array
    {
        return $this->withConnection(function (PDO $connection) use ($criteria) {
            $query = "SELECT * FROM {$this->table}";
            $params = [];

            if (!empty($criteria)) {
                $conditions = [];
                foreach ($criteria as $key => $value) {
                    $conditions[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $stmt = $connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        });
    }

    public function count(array $conditions = []): int
    {
        return $this->withConnection(function (PDO $connection) use ($conditions) {
            $query = "SELECT COUNT(*) FROM {$this->table}";
            $params = [];

            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $key => $value) {
                    $whereClauses[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $query .= " WHERE " . implode(' AND ', $whereClauses);
            }

            $stmt = $connection->prepare($query);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        });
    }

    public function create(array $data): int
    {
        return $this->withConnection(function (PDO $connection) use ($data) {
            $columns = implode(',', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));

            $query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $connection->prepare($query);
            $stmt->execute($data);

            return (int) $connection->lastInsertId();
        });
    }

    public function update(int $id, array $data): bool
    {
        return $this->withConnection(function (PDO $connection) use ($id, $data) {
            $setClauses = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                $setClauses[] = "$key = :set_$key";
                $params["set_$key"] = is_bool($value) ? (int)$value : $value;
            }

            $query = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . " WHERE id = :id";
            $params['id'] = $id;

            $stmt = $connection->prepare($query);
            return $stmt->execute($params);
        });
    }

    public function delete(int $id): bool
    {
        return $this->withConnection(function (PDO $connection) use ($id) {
            $stmt = $connection->prepare("DELETE FROM {$this->table} WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        });
    }

    public function findWithQuery(array $select = ['*'], array $conditions = [], array $joins = [], array $order = [], string $having = '', int $limit = 0, int $offset = 0): array
    {
        return $this->withConnection(function (PDO $connection) use ($select, $conditions, $joins, $order, $having, $limit, $offset) {
            $query = "SELECT " . implode(', ', $select) . " FROM {$this->table}";

            if (!empty($joins)) {
                foreach ($joins as $join) {
                    $query .= " {$join['type']} JOIN {$join['table']} ON {$join['on']}";
                }
            }

            $params = [];
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $key => $value) {
                    $whereClauses[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
                $query .= " WHERE " . implode(' AND ', $whereClauses);
            }

            if (!empty($having)) {
                $query .= " HAVING $having";
            }

            if (!empty($order)) {
                $orderClauses = [];
                foreach ($order as $column => $direction) {
                    $orderClauses[] = "$column $direction";
                }
                $query .= " ORDER BY " . implode(', ', $orderClauses);
            }

            if ($limit > 0) {
                $query .= " LIMIT $limit";
                if ($offset > 0) {
                    $query .= " OFFSET $offset";
                }
            }

            $stmt = $connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        });

    }

    public function executeRawQuery(string $query, array $params = []): array
    {
        return $this->withConnection(function (PDO $connection) use ($query, $params) {
            $stmt = $connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        });
    }

    public function bulkInsert(array $data): int
    {
        return $this->withConnection(function (PDO $connection) use ($data) {
            if (empty($data)) {
                throw new \InvalidArgumentException("No data provided for bulk insert.");
            }

            $columns = array_keys($data[0]);
            $columnList = implode(',', $columns);

            $placeholders = [];
            $flatValues = [];
            foreach ($data as $row) {
                $row = array_map(function ($value) {
                    return is_bool($value) ? (int)$value : $value;
                }, $row);

                $placeholders[] = '(' . implode(',', array_fill(0, count($row), '?')) . ')';
                $flatValues = array_merge($flatValues, array_values($row));
            }

            $query = "INSERT INTO {$this->table} ($columnList) VALUES " . implode(',', $placeholders);

            $stmt = $connection->prepare($query);
            $stmt->execute($flatValues);

            return $stmt->rowCount();
        });
    }
}