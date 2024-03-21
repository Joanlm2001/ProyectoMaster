<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFavs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pokemon_id',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function obtenerPokemon()
    {
        // Lógica para obtener el Pokémon de la API usando `id_pokemon` y `fuente_pokemon`
    }
}
