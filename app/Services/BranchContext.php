<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BranchContext
{
    protected ?int $branchId = null;
    protected bool $canSeeAll = false;

    public function setFromUser(): void
    {
        $user = Auth::user();
        if (!$user) return;

        if ($user->role === 'firma_sahibi') {
            $this->canSeeAll = true;
            $this->branchId = Session::get('active_branch_id', null);
        } else {
            $this->canSeeAll = false;
            $this->branchId = $user->branch_id;
        }
    }

    public function getBranchId(): ?int { return $this->branchId; }
    public function canSeeAll(): bool { return $this->canSeeAll && $this->branchId === null; }

    public function switchTo(?int $branchId): void
    {
        $this->branchId = $branchId;
        Session::put('active_branch_id', $branchId);
    }

    public function applyTo($query, string $column = 'branch_id')
    {
        if ($this->branchId !== null) $query->where($column, $this->branchId);
        return $query;
    }
}
