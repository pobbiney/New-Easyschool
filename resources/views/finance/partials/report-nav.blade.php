<div class="sv-nav">
    @foreach([
        'school-vault-dashboard'=>'Dashboard',
        'school-vault-collections'=>'Feeding Fee Collections',
        'school-vault-transactions'=>'Transaction History',
        'school-vault-reconciliation'=>'Daily Reconciliation',
        'school-vault-statement'=>'Vault Statement',
    ] as $routeName => $label)
        @if(app(\App\Services\UserAccessService::class)->can($routeName))
            <a href="{{ route($routeName) }}" class="{{ request()->routeIs($routeName) ? 'active' : '' }}">{{ $label }}</a>
        @endif
    @endforeach
</div>
