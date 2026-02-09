<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'price',
        'thumbnail',
        'teacher_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'orders', 'course_id', 'user_id')
                    ->where('payment_status', 'paid')
                    ->withTimestamps();
    }

    public function isPurchasedBy(User $user)
    {
        return $this->students()->where('user_id', $user->id)->exists();
    }
}