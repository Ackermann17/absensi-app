<?php

use App\Models\Employee;
use Livewire\Volt\Component;
use Livewire\WithPagination;

// Provide the paginated employees for the view directly
$employees = Employee::with('user')->paginate(10);

new class extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public function mount(): void
    {
        // nothing required here for the static listing
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <h2 class="text-lg font-medium">Employees</h2>

                <div class="mt-4">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Employee Code</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                <th class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($employees as $employee)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $employee->user->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $employee->user->email ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $employee->employee_code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $employee->phone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $employee->position }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $employee->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
