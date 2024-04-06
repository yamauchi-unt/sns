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
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ユーザ新規登録
    public static function register($validated)
    {
        $user = User::create([
            'user_id' => $validated['user_id'],
            'user_name' => $validated['user_name'],
            'password' => Hash::make($validated['password']),
        ]);

        return $user;
    }

    // プロフィール編集
    public function myprofileUpdate(array $validated)
    {
        // 'new_password'の存在チェック
        if (!empty($validated['new_password'])) {
            // 存在したらハッシュ化
            $this->password = Hash::make($validated['new_password']);
        } else {
            // 存在しなかったらキーを除外
            unset($validated['new_password']);
        }

        // 更新
        $this->update($validated);
    }
}
