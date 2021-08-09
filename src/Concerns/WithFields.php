<?php

namespace Jackardios\JsonApiRequest\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jackardios\JsonApiRequest\Exceptions\InvalidFieldQuery;
use Jackardios\JsonApiRequest\Exceptions\DefaultTableIsNotDefined;

trait AddsFields
{
    protected ?Collection $requestedFields;
    protected ?Collection $allowedFields;

    protected ?string $defaultTable;

    private static string $fieldsArrayValueDelimiter = ',';

    public static function setFieldsArrayValueDelimiter(string $fieldsArrayValueDelimiter): void
    {
        static::$fieldsArrayValueDelimiter = $fieldsArrayValueDelimiter;
    }

    public static function getFieldsArrayValueDelimiter(): string
    {
        return static::$fieldsArrayValueDelimiter;
    }

    public function setDefaultTable(string $table): self {
        $this->defaultTable = $table;

        return $this;
    }

    public function getDefaultTable(): ?string {
        return $this->defaultTable;
    }

    public function setAllowedFields($fields): self
    {
        $fields = is_array($fields) ? $fields : func_get_args();

        $this->allowedFields = collect($fields)
            ->map(function (string $fieldName) {
                return $this->prependField($fieldName);
            });

        $this->ensureAllFieldsExist();

        return $this;
    }

    public function getAllowedFields(): ?Collection {
        return $this->allowedFields;
    }

    public function fields(): Collection
    {
        if ($this->requestedFields) {
            return $this->requestedFields;
        }

        $fieldsParameterName = config('api-request-bag.parameters.fields');
        $fieldsPerTable = collect($this->getRequestData($fieldsParameterName));

        if ($fieldsPerTable->isEmpty()) {
            return collect();
        }

        $this->requestedFields = $fieldsPerTable
            ->map(function ($fields): array {
                return array_unique(explode(static::getFieldsArrayValueDelimiter(), $fields));
            })
            ->filter();

        return $this->requestedFields;
    }

    protected function ensureAllFieldsExist(): self
    {
        $requestedFields = $this->fields()
            ->map(function ($fields, $model) {
                $tableName = $model;

                return $this->prependFieldsWithTableName($fields, $tableName);
            })
            ->flatten()
            ->unique();

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
        if (!$table) {
            $table = $this->getDefaultTable();
            if (!$table) {
                throw new DefaultTableIsNotDefined();
            }
        }

        if (Str::contains($field, '.')) {
            // Already prepended

            return $field;
        }

        return "{$table}.{$field}";
    }
}
