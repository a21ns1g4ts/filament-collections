<?php

namespace App\Http\Requests;

use A21ns1g4ts\FilamentCollections\Models\CollectionConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $collectionKey = $this->route('collectionKey');
        $recordId = $this->route('id');
        $config = CollectionConfig::where('key', $collectionKey)->first();

        if (! $config) {
            return [];
        }

        $rules = ['payload' => 'required|array'];

        if (is_array($config->schema)) {
            foreach ($config->schema as $field) {
                $fieldName = "payload.{$field['name']}";
                $fieldRules = [];

                if (isset($field['required']) && $field['required']) {
                    $fieldRules[] = 'required';
                }

                if (isset($field['unique']) && $field['unique']) {
                    $uniqueRule = Rule::unique('collection_data', "payload->{$field['name']}");
                    if ($recordId) {
                        $uniqueRule->ignore($recordId);
                    }
                    $fieldRules[] = $uniqueRule;
                }

                $rules[$fieldName] = $fieldRules;
            }
        }

        return $rules;
    }
}