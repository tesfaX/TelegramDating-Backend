<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dislike extends Model
{
    use HasFactory;
    protected $fillable = ['dislike_by', 'dislike_for'];

    public function dislikeBy() {
        $this->belongsTo(User::class, 'disliked_by');
    }

    public function dislikeFor() {
        $this->belongsTo(User::class, 'dislike_for');
    }

}
