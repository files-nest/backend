<?php

namespace App\Models;

use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    /** @use HasFactory<FileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'create_datetime',
        'checksum',
    ];

    protected $casts = [
        'create_datetime' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = [
        'user_id',
        'path',
        'status_id',
        'status',
    ];

    protected $appends = [
        'status_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function getStatusNameAttribute(): ?string
    {
        return $this->status?->name;
    }

    public function getDestinationPathOnDisk(): string
    {
        return sprintf('%s/%04d/%02d/%02d',
            $this->user->email,
            $this->create_datetime->year,
            $this->create_datetime->month,
            $this->create_datetime->day
        );
    }

    public function getDestinationPathNameOnDisk(): string
    {
        return sprintf('%s/%s',
            $this->getDestinationPathOnDisk(),
            $this->name,
        );
    }
}
