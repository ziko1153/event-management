<?php

namespace App\Service;

class ValidationService
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                if (!$this->validateField($data[$field] ?? null, $rule, $data, $field)) {
                    $this->errors[$field][] = $this->getErrorMessage($field, $rule);
                }
            }
        }

        return empty($this->errors);
    }

    private function validateField($value, string $rule, array $data, string $field): bool
    {
        if (strpos($rule, ':') !== false) {
            [$rule, $params] = explode(':', $rule, 2);
            $params = explode(',', $params);
        }

        return match ($rule) {
            'required' => !empty($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min' => strlen($value) >= (int)$params[0],
            'match' => isset($data[$params[0]]) && $value === $data[$params[0]],
            'unique' => $this->isUnique($params[0], $params[1] ?? $field, $value),
            default => true,
        };
    }

    private function isUnique(string $table, string $column, $value): bool
    {
        $db = \Config\Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = :value");
        $stmt->execute([':value' => $value]);
        
        return (int)$stmt->fetchColumn() === 0;
    }

    private function getErrorMessage(string $field, string $rule): string
    {
        if (strpos($rule, ':') !== false) {
            [$rule, $param] = explode(':', $rule);
        }

        return match ($rule) {
            'required' => ucfirst($field) . ' is required',
            'email' => 'Please enter a valid email address',
            'min' => ucfirst($field) . ' must be at least ' . $param . ' characters',
            default => ucfirst($field) . ' is invalid',
        };
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }
        $firstField = array_key_first($this->errors);
        return $this->errors[$firstField][0];
    }
}