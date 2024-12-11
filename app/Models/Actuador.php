<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Actuador extends Eloquent
{
    use HasFactory;
    protected $fillable = ['nombre', 'tipo', 'imagen', 'motivo', 'fecha'];

    protected $primaryKey = '_id'; // Especifica que la clave primaria es '_id'

    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
