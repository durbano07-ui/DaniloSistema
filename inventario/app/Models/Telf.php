<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Telf extends Model
{
    protected $table = 'telf';
    protected $primaryKey = 'id_telf';

    protected $fillable = ['numero', 'telefonable_id', 'telefonable_type'];

    public function telefonable()
    {
        return $this->morphTo();
    }
}