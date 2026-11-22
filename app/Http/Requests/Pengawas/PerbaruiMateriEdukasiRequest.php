<?php

namespace App\Http\Requests\Pengawas;

class PerbaruiMateriEdukasiRequest extends SimpanMateriEdukasiRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return $this->aturan(sebagian: true);
    }
}
