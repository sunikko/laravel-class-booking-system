<?php

use App\Models\User;

it('returns a successful response', function () {
    $response = $this->get('/');
    $response->assertStatus(200);

    $user = User::factory()->create();
    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(302);
    $response->assertRedirect('/bookings');
});
