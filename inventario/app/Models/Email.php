<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    protected $table = 'emails';
    protected $primaryKey = 'id_email';

    protected $fillable = ['correo', 'emailable_id', 'emailable_type'];

    public function emailable()
    {
        return $this->morphTo();
    }
}