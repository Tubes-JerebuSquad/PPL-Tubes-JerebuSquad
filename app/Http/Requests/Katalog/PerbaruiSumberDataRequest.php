<?php

namespace App\Http\Requests\Katalog;

class PerbaruiSumberDataRequest extends SimpanSumberDataRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->aturan(sebagian: true);
    }
}
