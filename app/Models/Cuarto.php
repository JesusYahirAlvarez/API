<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Cuarto extends Eloquent
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'TestCuartos';

    protected $fillable = ['nombre', 'ubicacion', 'sensores', 'actuadores','usuarios'];

    public function sensores()
    {
        return $this->embedsMany(Sensor::class);
    }

    public function actuadores()
    {
        return $this->embedsMany(Actuador::class);
    }

    public function usuarios()
    {
        return $this->embedsMany(User::class);
    }

}
