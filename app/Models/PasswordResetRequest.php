<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    protected $table = 'password_reset_requests';

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
        'decided_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** Admin yang menyetujui/menolak (tabel `user`). */
    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /** Masih menunggu keputusan admin dan belum kedaluwarsa. */
    public function scopeAktif($q)
    {
        return $q->where('status', 'pending')->where('expires_at', '>', now());
    }
}
