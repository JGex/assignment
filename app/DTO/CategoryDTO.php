<?php

namespace App\DTO;

use App\DTO\Exceptions\CategoryValidationException;
use Illuminate\Support\Facades\Validator;

class CategoryDTO
{
    private const array VALIDATION_RULES = [
        'name' => 'required|string',
    ];
    public ?string $name = null;

    public function __construct(?string $name)
    {
        $this->name = $name ?? null;
    }

    /**
     * @throws CategoryValidationException
     */
    public static function fromFakeAPI(?string $name): self
    {
        $categoryDTO = new self($name);

        $validation = Validator::make($categoryDTO->toArray(), self::VALIDATION_RULES);

        if (!$validation->errors()->isEmpty()) {
            throw new CategoryValidationException($name ?? '', $validation->errors()->all());
        }

        return $categoryDTO;
    }

    /**
     * @return array<string, null|string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
