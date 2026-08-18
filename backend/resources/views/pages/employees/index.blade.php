<?php

use App\Models\Employee;
use App\Models\User; // Tambahkan import User
use Illuminate\Support\Facades\Hash; // Tambahkan import Hash untuk password
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public bool $isFormOpen = false;
    public bool $isEditing = false;
    public ?int $employeeId = null;

    public string $name = ''; // Tambahan state nama
    public string $employee_code = '';
    public string $phone = '';
    public string $position = '';
    public string $status = 'active';

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'], // Validasi nama
            'employee_code' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('employees', 'employee_code')->ignore($this->employeeId)
            ],
            'phone' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }

    public function openCreateForm(): void
    {
        $this->resetValidation();
        // Tambahkan 'name' ke dalam reset
        $this->reset(['name', 'employee_code', 'phone', 'position', 'status', 'employeeId']);
        $this->isEditing = false;
        $this->isFormOpen = true;
    }

    public function edit(Employee $employee): void
    {
        $this->resetValidation();
        $this->employeeId = $employee->id;
        
        // Ambil nama dari relasi User
        $this->name = $employee->user->name ?? ''; 
        
        $this->employee_code = $employee->employee_code;
        $this->phone = $employee->phone ?? '';
        $this->position = $employee->position ?? '';
        $this->status = $employee->status;

        $this->isEditing = true;
        $this->isFormOpen = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if (! $this->isEditing) {
            // 1. Buat User baru khusus untuk murid ini
            $user = User::create([
                'name' => $this->name,
                // Email dibuat otomatis dari employee_code agar unik
                'email' => $this->employee_code . '@student.local', 
                'password' => Hash::make('password123'), // Password default
            ]);

            // 2. Buat Employee dengan user_id dari User yang baru dibuat
            Employee::create([
                'user_id' => $user->id,
                'employee_code' => $this->employee_code,
                'phone' => $this->phone,
                'position' => $this->position,
                'status' => $this->status,
            ]);
            
        } else {
            // Logika Edit / Update
            $employee = Employee::find($this->employeeId);
            $employee->update([
                'employee_code' => $this->employee_code,
                'phone' => $this->phone,
                'position' => $this->position,
                'status' => $this->status,
            ]);

            // Update juga nama di tabel User
            if ($employee->user) {
                $employee->user->update([
                    'name' => $this->name,
                ]);
            }
        }

        $this->isFormOpen = false;
        // Tambahkan 'name' ke dalam reset
        $this->reset(['name', 'employee_code', 'phone', 'position', 'status', 'employeeId']);
    }

    public function delete(Employee $employee): void
    {
        // Opsional: Jika ingin user-nya ikut terhapus, bisa gunakan $employee->user->delete();
        $employee->delete();
    }

    public function with(): array
    {
        return [
            'employees' => Employee::with('user')->latest()->paginate(10),
        ];
    }
};
?>

<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Employee Management
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex justify-end items-center mb-6">
                <button wire:click="openCreateForm" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 transition">
                    + Add New Employee
                </button>
            </div>

            <!-- Form Section -->
            @if($isFormOpen)
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm mb-6 transition-all">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">
                        {{ $isEditing ? 'Edit Employee' : 'Create New Employee' }}
                    </h3>
                    
                    <form wire:submit="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <!-- INPUT NAMA (BARU DITAMBAHKAN) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input type="text" wire:model="name" placeholder="Contoh: Budi Santoso" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Employee Code</label>
                                <input type="text" wire:model="employee_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('employee_code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                <input type="text" wire:model="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Position / Class</label>
                                <input type="text" wire:model="position" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error('position') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                                <select wire:model="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" wire:click="$set('isFormOpen', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                                {{ $isEditing ? 'Update Employee' : 'Save Employee' }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Table Section -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Emp. Code</th>
                                    
                                    <!-- HEADER NAMA DITAMBAHKAN DI SINI -->
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Position</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($employees as $employee)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $employee->employee_code }}</td>
                                        
                                        <!-- DATA NAMA DITAMBAHKAN DI SINI -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $employee->user->name ?? '-' }}</td>
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $employee->position ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $employee->phone ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $employee->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                                {{ ucfirst($employee->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium gap-3 flex">
                                            <button wire:click="edit({{ $employee->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Edit</button>
                                            <button wire:click="delete({{ $employee->id }})" wire:confirm="Yakin ingin menghapus pegawai ini?" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <!-- colspan diubah jadi 6 karena tambah 1 kolom nama -->
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Belum ada data pegawai.</td> 
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>