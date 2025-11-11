<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPassword extends Model
{
    protected $table = 'student_passwords'; // Your actual table name
    protected $primaryKey = 'password_id';  // Your custom primary key
    protected $fillable = ['email_address', 'password_hashed']; // Fields you allow to be inserted
}