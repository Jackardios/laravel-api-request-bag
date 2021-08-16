<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jackardios\JsonApiRequest\Exceptions\InvalidFieldQuery;
use Jackardios\JsonApiRequest\Exceptions\DefaultTableIsNotDefined;

trait HasFields
{
    protected ?Collection $requestedFields = null;
    protected ?Collection $allowedFields = null;

    protected ?string $defaultTable = null;

    private static string $fieldsArrayValueDelimiter = ',';

    public static function setFieldsArrayValueDelimiter(string $fieldsArrayValueDelimiter): void
    {
        static::$fieldsArrayValueDelimiter = $fieldsArrayValueDelimiter;
    }

    public static function getFieldsArrayValueDelimiter(): string
    {
        return static::$fieldsArrayValueDelimiter;
    }

    /**
     * 'defaultTable' is used as a default table for allowed fields
     *
     * @return string
     */
    protected function defaultTable(): string
    {
        return "";
    }

    public function setDefaultTable(string $table): self
    {
        $this->defaultTable = $table;

        return $this;
    }

    public function getDefaultTable(): ?string
    {
        if (!$this->defaultTable) {
            $defaultTableFromCallback = $this->defaultTable();
            if (!$defaultTableFromCallback) {
                throw new DefaultTableIsNotDefined();
            }
            $this->setDefaultTable($defaultTableFromCallback);
        }

        return $this->defaultTable;
    }

    protected function allowedFields(): array
    {
        return [];
    }

    public function setAllowedFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->allowedFields = collect($fields)
            ->map(function (string $fieldName) {
                return $this->prependField($fieldName);
            });

        $this->ensureAllFieldsAllowed();

        return $this;
    }

    public function getAllowedFields(): ?Collection
    {
        if (!($this->allowedFields instanceof Collection)) {
            $allowedFieldsFromCallback = $this->allowedFields();

            if ($allowedFieldsFromCallback) {
                $this->setAllowedFields($allowedFieldsFromCallback);
            }
        }

        return $this->allowedFields;
    }

    public function fields(): Collection
    {
        if ($this->requestedFields instanceof Collection) {
            return $this->requestedFields;
        }

        // ensure all fields allowed
        $this->getAllowedFields();

        $fieldsParameterName = config('json-api-request.parameters.fields');
        $fieldsPerTable = collect($this->getRequestData($fieldsParameterName));

        if ($fieldsPerTable->isEmpty()) {
            return collect();
        }

        $this->requestedFields = $fieldsPerTable
            ->map(function ($fields): array {
                if (is_string($fields)) {
                    $fields = explode(static::getFieldsArrayValueDelimiter(), $fields);
                }
                return collect($fields)
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            })
            ->filter();

        return $this->requestedFields;
    }

    protected function ensureAllFieldsAllowed(): self
    {
        $requestedFields = $this->fields()
            ->map(function ($fields, $model) {
                $tableName = $model;

                return $this->prependFieldsWithTableName($fields, $tableName);
            })
            ->flatten()
            ->unique()
            ->values();

        $unknownFields = $requestedFields->diff($this->allowedFields);

        if ($unknownFields->isNotEmpty()) {
            throw InvalidFieldQuery::fieldsNotAllowed($unknownFields, $this->allowedFields);
        }

        return $this;
    }

    protected function prependFieldsWithTableName(array $fields, string $tableName): array
    {
        return array_map(function ($field) use ($tableName) {
            return $this->prependField($field, $tableName);
        }, $fields);
    }

    protected function prependField(string $field, ?string $table = null): string
    {
        if (Str::contains($field, '.')) {
            // Already prepended

            return $field;
        }

        $table = $table ?? $this->getDefaultTable();

        return "{$table}.{$field}";
    }
}
