<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class SchoolVault extends Model
{
    public const ACCOUNT_CODE = 'SCHOOL_VAULT';

    public const STATUS_ACTIVE = 'Active';

    public const STATUS_INACTIVE = 'Inactive';

    public const CURRENCY = 'GHS';

    protected $guarded = ['*'];

    protected static function booted(): void
    {
        static::updating(function (self $vault) {
            if ($vault->isDirty(['account_code', 'currency'])) {
                throw new LogicException('The School Vault account identity and currency cannot be changed.');
            }
        });
        static::deleting(fn () => throw new LogicException('The School Vault account cannot be deleted.'));
    }

    public function transactions()
    {
        return $this->hasMany(SchoolVaultTransaction::class);
    }
}
