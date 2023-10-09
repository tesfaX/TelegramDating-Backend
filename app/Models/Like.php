<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = ['like_by', 'like_for'];

    public function likeBy() {
        return $this->belongsTo(User::class, 'like_by');
    }

    public function likeFor() {
        return $this->belongsTo(User::class, 'like_for');
    }

}
