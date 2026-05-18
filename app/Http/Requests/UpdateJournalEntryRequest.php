<?php

namespace App\Http\Requests;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJournalEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('journal_entry')) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'string', $this->minimumWordCount(50)],
            'is_public' => ['sometimes', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['sometimes', 'array', 'max:5'],
            'tags.*' => ['required', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('tags')) {
            return;
        }

        $this->merge([
            'tags' => collect($this->input('tags', []))
                ->filter(fn (mixed $tag): bool => filled($tag))
                ->values()
                ->all(),
        ]);
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.max' => 'The title may not be longer than 255 characters.',
            'tags.array' => 'Tags must be submitted as a list.',
            'tags.max' => 'You may add up to 5 tags.',
            'tags.*.required' => 'Each tag must have a value.',
            'tags.*.string' => 'Each tag must be text.',
            'tags.*.max' => 'Each tag may not be longer than 50 characters.',
        ];
    }

    private function minimumWordCount(int $minimum): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($minimum): void {
            $wordCount = str_word_count(strip_tags((string) $value));

            if ($wordCount < $minimum) {
                $fail("The {$attribute} must be at least {$minimum} words.");
            }
        };
    }
}
