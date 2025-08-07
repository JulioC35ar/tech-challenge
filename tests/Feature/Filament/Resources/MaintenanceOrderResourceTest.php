<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\{actingAs, get, post};

uses(RefreshDatabase::class);

beforeEach(function () {
    Auth::logout();
});

it('redirects guests from maintenance order index', function () {
    get('/admin/maintenance-orders')->assertRedirect('/admin/login');
});

it('allows supervisors to create maintenance orders', function () {
    $supervisor = User::factory()->create(['role' => 'supervisor']);
    actingAs($supervisor);

    post('/admin/maintenance-orders', [
        'title' => 'Test Order',
        'asset_id' => 1,
        'priority' => 'high',
    ])->assertSessionHasNoErrors();
});

it('does not allow technicians to access the create form', function () {
    $technician = User::factory()->create(['role' => 'technician']);
    actingAs($technician);

    get('/admin/maintenance-orders/create')->assertForbidden();
});