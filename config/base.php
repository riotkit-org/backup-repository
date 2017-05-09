<?php declare(strict_types=1);

function getConfigurationValue(string $name, $default, $allowEmptyString = false, $isJson = false)
{
    $value = $default;

    if (array_key_exists($name, $_SERVER)) {
        $value = $_SERVER[$name];
    }

    if ($value === '' && !$allowEmptyString) {
        $value = null;

    } elseif ($value == 'null') {
        $value = null;

    } elseif (is_string($value) && $isJson === true) {
        $value = json_decode($value, true);
    }

    return $value;
}
