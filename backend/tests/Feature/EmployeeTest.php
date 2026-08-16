<?php

use App\Models\User;
use App\Models\Employee;
use Livewire\Volt\Volt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Membuat user admin tiruan dan melakukan login
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can render the employee management page', function () {
    $this->get(route('employees.index'))
        ->assertStatus(200)
        ->assertSee('Employee Management');
});

it('can create a new employee via Volt component', function () {
    Volt::test('pages.employees.index') // <-- SUDAH DIPERBAIKI DI SINI
        ->call('openCreateForm')
        ->set('employee_code', 'EMP-2026')
        ->set('phone', '081234567890')
        ->set('position', 'Senior Developer')
        ->set('status', 'active')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('employees', [
        'employee_code' => 'EMP-2026',
        'position' => 'Senior Developer',
        'user_id' => $this->user->id,
    ]);
});

it('can update an existing employee via Volt component', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
        'position' => 'Junior Developer',
    ]);

    Volt::test('pages.employees.index') // <-- SUDAH DIPERBAIKI DI SINI
        ->call('edit', $employee->id)
        ->set('position', 'Tech Lead')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'position' => 'Tech Lead',
    ]);
});

it('can delete an employee via Volt component', function () {
    $employee = Employee::factory()->create([
        'user_id' => $this->user->id,
    ]);

    Volt::test('pages.employees.index') // <-- SUDAH DIPERBAIKI DI SINI
        ->call('delete', $employee->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('employees', [
        'id' => $employee->id,
    ]);
});