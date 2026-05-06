<?php

use Illuminate\Support\Facades\Broadcast;

// If your admin is a different model, you might need to specify the guard
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['web', 'admin']]); // Add your admin guard here