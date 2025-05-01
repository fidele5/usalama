<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'alert_id', 'user_id', 'parent_id', 'message', 
        'attachment', 'read_at', 'is_thread'
    ];

    public function markAsRead() {
        $this->update(['read_at' => now()]);
    }

    // Original message this replies to
    public function parent()
    {
        return $this->belongsTo(ChatMessage::class, 'parent_id');
    }

    // All replies to this message
    public function replies()
    {
        return $this->hasMany(ChatMessage::class, 'parent_id')
                    ->where('is_thread', true);
    }

    // Mark as thread starter
    public function startThread(): self
    {
        $this->update(['is_thread' => true]);
        return $this;
    }
}
