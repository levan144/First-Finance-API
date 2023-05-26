<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DifferentAccountIds implements Rule
{
    private $otherAccountId;

    public function __construct($otherAccountId)
    {
        $this->otherAccountId = $otherAccountId;
    }

    public function passes($attribute, $value)
    {
        return $value != $this->otherAccountId;
    }

    public function message()
    {
        return 'The :attribute and recipient account IDs must be different.';
    }
}
