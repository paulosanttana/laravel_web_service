<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public function getResults($name = null)
    {   // verifica se está passando nome na pesquisa, Se sim traz tudo!
        if (!$name)
            return $this->get();

        // Se não, faz o like
        return $this->where('name', 'LIKE', "%{$name}%")
                ->get();
    }
}
