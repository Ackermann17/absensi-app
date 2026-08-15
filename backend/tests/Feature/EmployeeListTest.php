<?php

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the employees page for authenticated users', function () {
    $user = User::factory()->create();

    $employee = Employee::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('employees.index'))
        ->assertOk()
        ->assertSee('Employees')
        ->assertSee($employee->employee_code);
});

it('redirects guest to login', function () {
    $this->get(route('employees.index'))
        ->assertRedirect('/login');
});