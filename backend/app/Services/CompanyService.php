<?php
namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyService
{
    public function store(array $data, $user): Company {
        return DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);

            if (isset($data['logo'])) {
                $data['logo'] = $data['logo']->store('companies/logos', 'public');
            }
            if (isset($data['cover_image'])) {
                $data['cover_image'] = $data['cover_image']->store('companies/covers', 'public');
            }

            return Company::create($data);
        });
    }

    public function update(array $data, Company $company): Company
    {
        return DB::transaction(function () use ($data, $company) {
            if (isset($data['name']) && $data['name'] !== $company->name) {
                $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
            }

            if (isset($data['logo'])) {
                if ($company->logo) {
                    Storage::disk('public')->delete($company->logo);
                }
                $data['logo'] = $data['logo']->store('companies/logos', 'public');
            }

            if (isset($data['cover_image'])) {
                if ($company->cover_image) {
                    Storage::disk('public')->delete($company->cover_image);
                }
                $data['cover_image'] = $data['cover_image']->store('companies/covers', 'public');
            }

            $company->update($data);

            return $company;
        });
    }
}