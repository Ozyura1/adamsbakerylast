<?php
/**
 * Form validation utilities
 * Centralized validation logic for common data types
 */

class Validator
{
    private $errors = [];

    /**
     * Validate required field
     */
    public function required($value, $fieldName)
    {
        $value = is_string($value) ? trim($value) : $value;
        
        if (empty($value)) {
            $this->errors[$fieldName] = ucfirst($fieldName) . ' is required';
            return false;
        }
        return true;
    }

    /**
     * Validate email
     */
    public function email($value, $fieldName)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$fieldName] = 'Invalid email format';
            return false;
        }
        return true;
    }

    /**
     * Validate password strength
     */
    public function password($value, $fieldName, $minLength = 8)
    {
        if (strlen($value) < $minLength) {
            $this->errors[$fieldName] = "Password must be at least $minLength characters";
            return false;
        }
        return true;
    }

    /**
     * Validate phone number
     */
    public function phone($value, $fieldName)
    {
        $value = InputSanitizer::sanitizePhone($value);
        
        if (strlen($value) < 10 || strlen($value) > 15) {
            $this->errors[$fieldName] = 'Invalid phone number';
            return false;
        }
        return true;
    }

    /**
     * Validate string length
     */
    public function length($value, $fieldName, $min = 0, $max = 255)
    {
        $length = strlen($value);
        
        if ($length < $min || $length > $max) {
            $this->errors[$fieldName] = "Field length must be between $min and $max characters";
            return false;
        }
        return true;
    }

    /**
     * Check if field value exists in array
     */
    public function inArray($value, $fieldName, $allowedValues)
    {
        if (!in_array($value, $allowedValues)) {
            $this->errors[$fieldName] = 'Invalid selection';
            return false;
        }
        return true;
    }

    /**
     * Get all validation errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check if validation passed
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Get first error message
     */
    public function getFirstError()
    {
        return empty($this->errors) ? '' : reset($this->errors);
    }
}
?>
