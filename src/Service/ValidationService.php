<?php

namespace App\Service;

class ValidationService
{
    private array $errors = [];
    private array $data = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->data = $data;

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];
                $ruleValue = $ruleParts[1] ?? null;

                // Handle file validation
                if (in_array($ruleName, ['image', 'type', 'size'])) {
                    if (!$this->validateFileRule($field, $ruleName, $ruleValue)) {
                        break;
                    }
                    continue;
                }

                // Handle regular field validation
                if (!$this->validateRule($field, $ruleName, $ruleValue)) {
                    break;
                }
            }
        }

        return empty($this->errors);
    }

    private function validateRule(string $field, string $rule, ?string $parameter = null): bool
    {
        $value = $this->data[$field] ?? null;

        switch ($rule) {
            case 'required':
                if (isset($_FILES[$field])) {
                    if ($_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
                        $this->addError($field, "The {$field} field is required");
                        return false;
                    }
                    return true;
                }
                if (empty($value)) {
                    $this->addError($field, "The {$field} field is required");
                    return false;
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, "The $field must be a number");
                    return false;
                }
                break;

            case 'min':
                if (strlen($value) < (int)$parameter) {
                    $this->addError($field, "The $field must be at least $parameter characters");
                    return false;
                }
                break;

            case 'max':
                if (strlen($value) > (int)$parameter) {
                    $this->addError($field, "The $field must not be greater than $parameter characters");
                    return false;
                }
                break;

            case 'exists':
                if (!$this->checkExists($value, $parameter)) {
                    $this->addError($field, "The selected $field not found in the database");
                    return false;
                }
                break;

            case 'date':
                if (!$this->isValidDate($value)) {
                    $this->addError($field, "The $field must be a valid date, format: YYYY-MM-DD");
                    return false;
                }
                break;

            case 'after':
                if (!$this->isDateAfter($value, $this->data[$parameter] ?? null)) {
                    $this->addError($field, "The $field must be after " . str_replace('_', ' ', $parameter));
                    return false;
                }
                break;

            case 'after_equal':
                if (!$this->isDateAfter($value, $this->data[$parameter] ?? null, true)) {
                    $this->addError($field, "The $field must be after " . str_replace('_', ' ', $parameter));
                    return false;
                }
                break;

            case 'image':
                if (!$this->isValidImage($field)) {
                    $this->addError($field, "The $field must be a valid image");
                    return false;
                }
                break;

            case 'type':
                if (!$this->isValidFileType($field, explode(',', $parameter))) {
                    $this->addError($field, "The $field must be of type: $parameter");
                    return false;
                }
                break;

            case 'size':
                if (!$this->isValidFileSize($field, $parameter)) {
                    $this->addError($field, "The $field must not be larger than $parameter");
                    return false;
                }
                break;

            case 'enum':
                if (!in_array($value, explode(',', $parameter))) {
                    $this->addError($field, "The selected $field is invalid");
                    return false;
                }
                break;
        }

        return true;
    }

    private function validateFileRule(string $field, string $rule, ?string $parameter = null): bool
    {
        if (!isset($_FILES[$field])) {
            return true; // Skip file validation if no file uploaded
        }

        $file = $_FILES[$field];

        switch ($rule) {
            case 'image':
                if (!$this->isValidImage($field)) {
                    $this->addError($field, "The {$field} must be a valid image");
                    return false;
                }
                break;

            case 'type':
                $allowedTypes = explode(',', $parameter);
                if (!$this->isValidFileType($field, $allowedTypes)) {
                    $this->addError($field, "The {$field} must be of type: {$parameter}");
                    return false;
                }
                break;

            case 'size':

                if (!$this->isValidFileSize($field, $parameter)) {
                    $this->addError($field, "The {$field} must not be larger than {$parameter}");
                    return false;
                }
                break;
        }

        return true;
    }

    private function isValidDate(string $date): bool
    {
        return (bool)strtotime($date);
    }

    private function isDateAfter(string $date1, ?string $date2, bool $isEqual = false): bool
    {
        if (!$date2) return true;

        if ($isEqual)
            return strtotime(datetime: $date1) >= strtotime($date2);

        return strtotime(datetime: $date1) > strtotime($date2);
        
    }

    private function checkExists(mixed $value, string $parameter): bool
    {
        [$table, $column, $additionalColumn, $additionalValue] = array_pad(explode(',', $parameter), 4, null);

        $query = "SELECT COUNT(*) FROM $table WHERE $column = :value";
        $params = [':value' => $value];

        if ($additionalColumn && $additionalValue) {
            $query .= " AND $additionalColumn = :additional_value";
            $params[':additional_value'] = $additionalValue;
        }

        try {
            $db = \Config\Database::getInstance();
            $connection = $db->getConnection();
            $stmt = $connection->prepare($query);
            $stmt->execute($params);
            return (bool)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            return false;
        }
    }

    private function isValidImage(string $field): bool
    {
        if (!isset($_FILES[$field])) return false;

        $file = $_FILES[$field];

        if (!$file['tmp_name']) return false;

        $imageInfo = @getimagesize($file['tmp_name']);

        return $imageInfo !== false;
    }

    private function isValidFileType(string $field, array $allowedTypes): bool
    {
        if (!isset($_FILES[$field])) return false;

        $file = $_FILES[$field];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        return in_array($extension, $allowedTypes);
    }

    private function isValidFileSize(string $field, string $maxSize): bool
    {
        if (!isset($_FILES[$field])) return false;

        $file = $_FILES[$field];
        $size = $file['size'];

        $max = $this->convertSizeToBytes($maxSize);

        return $size <= $max;
    }

    private function convertSizeToBytes(string $size): int
    {
        $unit = strtolower(substr($size, -2));
        $value = (int)substr($size, 0, -2);

        switch ($unit) {
            case 'kb':
                return $value * 1024;
            case 'mb':
                return $value * 1024 * 1024;
            case 'gb':
                return $value * 1024 * 1024 * 1024;
            default:
                return $value;
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): string
    {
        return reset($this->errors) ?: '';
    }
}