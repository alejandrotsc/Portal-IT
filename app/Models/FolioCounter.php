<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolioCounter extends Model
{
    protected $table = 'folio_counters';


    protected $fillable = [
        'prefijo',
        'ultimo_valor'
    ];


    protected $casts = [
        'ultimo_valor' => 'integer'
    ];
}