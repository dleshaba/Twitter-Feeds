<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'author_name',
    'username',
    'content',
    'avatar_url',
    'posted_at',
])]
class Post extends Model
{
    //
}