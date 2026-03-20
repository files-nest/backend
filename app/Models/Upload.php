<?php

namespace App\Models;

use Database\Factories\UploadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Upload extends Model
{
    /** @use HasFactory<UploadFactory> */
    use HasFactory;

    protected $fillable = [
        'number',
        'file_id',
        'status_id',
    ];

    protected $hidden = [
        'file_id',
        'status_id',
        'status',
    ];

    protected $appends = [
        'status_name',
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function getStatusNameAttribute(): ?string
    {
        return $this->status?->name;
    }
}
