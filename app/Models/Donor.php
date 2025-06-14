<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'donor_code',
        'health_questions',
        'is_eligible',
        'rejection_reason',
        'status',
        'donation_date',
        'next_eligible_date',
        'notes',
        'approved_at',
        'completed_at',
        'alamat',             // Tambahan baru
        'lokasi_id'           // Tambahan baru
    ];

    protected $casts = [
        'health_questions' => 'array',
        'is_eligible' => 'boolean',
        'donation_date' => 'datetime',
        'next_eligible_date' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Lokasi
     */
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    /**
     * Check if user can donate again
     */
    public static function canDonateAgain($userId)
    {
        $lastDonation = self::where('user_id', $userId)
            ->where('status', 'completed')
            ->latest('donation_date')
            ->first();

        if (!$lastDonation) {
            return true; // Belum pernah donor
        }

        $nextEligibleDate = $lastDonation->donation_date->addWeeks(8);
        return Carbon::now()->gte($nextEligibleDate);
    }

    /**
     * Get next eligible date
     */
    public static function getNextEligibleDate($userId)
    {
        $lastDonation = self::where('user_id', $userId)
            ->where('status', 'completed')
            ->latest('donation_date')
            ->first();

        if (!$lastDonation) {
            return null;
        }

        return $lastDonation->donation_date->addWeeks(8);
    }

    /**
     * Generate donor code
     */
    public static function generateDonorCode()
    {
        $date = Carbon::now()->format('Ymd');
        $lastCode = self::whereDate('created_at', Carbon::today())
            ->latest('id')
            ->first();

        $sequence = $lastCode ? (int)substr($lastCode->donor_code, -3) + 1 : 1;
        
        return 'DON' . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
