<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    use HasFactory;

    protected $table = 'amicizie';
    public $incrementing = false;
    protected $primaryKey = null;
    const UPDATED_AT = null;

    protected $fillable = [
        'id_utente_richiedente',
        'id_utente_ricevente',
        'stato'
    ];

    public function richiedente() {
        return $this->belongsTo(User::class, 'id_utente_richiedente');
    }

    public function ricevente() {
        return $this->belongsTo(User::class, 'id_utente_ricevente');
    }
}
