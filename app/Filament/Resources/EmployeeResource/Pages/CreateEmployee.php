<?php namespace App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
class CreateEmployee extends CreateRecord {
    protected static string $resource = EmployeeResource::class;
    protected function handleRecordCreation(array $data): Model {
        if (empty($data['employee_id'])) {
            $count = \App\Models\Employee::count() + 1;
            $data['employee_id'] = 'EMP-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }
        return parent::handleRecordCreation($data);
    }
}
