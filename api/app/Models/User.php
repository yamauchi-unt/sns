<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    // ユーザ新規登録
    public static function register(array $value)
    {
        $user = self::create([
            'user_id' => $value['user_id'],
            'user_name' => $value['user_name'],
            'password' => Hash::make($value['password']),
        ]);

        return $user;
    }

    // プロフィール編集
    public function updateMyprofile(array $value)
    {
        // 'new_password'の値があるか判定
        if (!empty($validated['new_password'])) {
            // 存在したらハッシュ化
            $this->password = Hash::make($value['new_password']);
        } else {
            // 存在しなかったらキーを除外
            unset($value['new_password']);
        }

        $this->update($value);
    }
}
