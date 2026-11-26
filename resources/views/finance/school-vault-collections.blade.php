@php
    $pageName='school-vault'; $subpageName='collections'; $money=fn($v)=>\App\Support\Money::ghs($v);
    $canReceipts=app(\App\Services\UserAccessService::class)->can('student-bills');
@endphp
@extends('layouts.app')
@section('css')
@include('partials._academic-ui-styles')
@include('finance.partials.report-styles')
@endsection
@section('content')
<div class="dashboard-main-body sv-shell">
    @include('partials._page-header',['section'=>'School Vault','crumbs'=>[['label'=>'Dashboard','url'=>route('school-vault-dashboard')],['label'=>'Feeding Fee Collections','active'=>true]],'title'=>'Feeding Fee Collections','subtitle'=>'Confirmed student payments and their authoritative Vault posting status'])
    @include('finance.partials.report-nav')

    <div class="sv-card mb-24"><div class="sv-card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3 col-xl-2"><label class="form-label">Academic year</label><select name="academic_year_id" class="form-select"><option value="">All years</option>@foreach($academicYears as $row)<option value="{{$row->id}}" @selected((int)($values['academic_year_id']??$academicPeriod['year_id'])===$row->id)>{{$row->name}}</option>@endforeach</select></div>
            <div class="col-md-3 col-xl-2"><label class="form-label">Academic term</label><select name="academic_term_id" class="form-select"><option value="">All terms</option>@foreach($academicTerms as $row)<option value="{{$row->id}}" @selected((int)($values['academic_term_id']??$academicPeriod['term_id'])===$row->id)>{{$row->name}}</option>@endforeach</select></div>
            <div class="col-md-3 col-xl-2"><label class="form-label">Class category</label><select name="class_category_id" class="form-select"><option value="">All categories</option>@foreach($classCategories as $row)<option value="{{$row->id}}" @selected(($values['class_category_id']??'')==$row->id)>{{$row->name}}</option>@endforeach</select></div>
            <div class="col-md-3 col-xl-2"><label class="form-label">Class</label><select name="school_class_id" class="form-select"><option value="">All classes</option>@foreach($classes as $row)<option value="{{$row->id}}" @selected(($values['school_class_id']??'')==$row->id)>{{$row->name}}</option>@endforeach</select></div>
            <div class="col-md-4 col-xl-2"><label class="form-label">Student name or ID</label><input name="student" value="{{$values['student']??''}}" class="form-control" placeholder="Search student"></div>
            <div class="col-md-4 col-xl-2"><label class="form-label">Payment status</label><select name="payment_status" class="form-select"><option value="">All statuses</option>@foreach(['Paid','Partial','Pending'] as $status)<option @selected(($values['payment_status']??'')===$status)>{{$status}}</option>@endforeach</select></div>
            <div class="col-md-4 col-xl-2"><label class="form-label">Payment method</label><select name="payment_method" class="form-select"><option value="">All methods</option>@foreach(['Cash','Bank','Paystack'] as $method)<option @selected(($values['payment_method']??'')===$method)>{{$method}}</option>@endforeach</select></div>
            <div class="col-md-3 col-xl-2"><label class="form-label">Payment from</label><input type="date" name="payment_from" value="{{$values['payment_from']??''}}" class="form-control"></div>
            <div class="col-md-3 col-xl-2"><label class="form-label">Payment to</label><input type="date" name="payment_to" value="{{$values['payment_to']??''}}" class="form-control"></div>
            <div class="col-md-3 col-xl-2"><button class="btn btn-primary-600 w-100"><i class="ri-filter-3-line"></i> Apply</button></div>
            <div class="col-md-3 col-xl-2"><a href="{{route('school-vault-collections')}}" class="btn btn-outline-secondary w-100">Reset</a></div>
        </form>
    </div></div>

    <div class="d-flex flex-wrap justify-content-end gap-2 mb-16">
        <a target="_blank" href="{{route('school-vault-collections-print',request()->query())}}" class="btn btn-outline-primary-600 btn-sm"><i class="ri-printer-line"></i> Print</a>
        <a href="{{route('school-vault-collections-pdf',request()->query())}}" class="btn btn-outline-primary-600 btn-sm"><i class="ri-file-pdf-2-line"></i> PDF</a>
        <a href="{{route('school-vault-collections-excel',request()->query())}}" class="btn btn-outline-primary-600 btn-sm"><i class="ri-file-excel-2-line"></i> Excel</a>
    </div>
    <div class="row gy-4 mb-24">
        @foreach([['Feeding Fee billed',$summary['billed']],['Confirmed student payments',$summary['confirmed']],['Posted Vault collections',$summary['posted']],['Outstanding optional bills',$summary['outstanding']]] as [$label,$value])
            <div class="col-sm-6 col-xl-3"><div class="sv-stat"><small>{{$label}}</small><strong>{{$money($value)}}</strong></div></div>
        @endforeach
        @foreach([['Collection percentage',number_format($summary['percentage'],1).'%'],['Students with bills',number_format($summary['students'])],['Fully paid',number_format($summary['fully_paid'])],['Partially paid / unpaid',number_format($summary['partially_paid']).' / '.number_format($summary['unpaid'])]] as [$label,$value])
            <div class="col-sm-6 col-xl-3"><div class="sv-stat"><small>{{$label}}</small><strong>{{$value}}</strong></div></div>
        @endforeach
    </div>
    <div class="sv-note mb-24"><i class="ri-information-line"></i> Confirmed payments and posted Vault collections are shown separately. Outstanding Feeding Fee bills are optional billing balances, not compulsory debt.</div>

    <div class="sv-card mb-24"><div class="sv-card-head"><div><h6>Student-level Feeding Fee billing</h6><p class="text-sm text-secondary-light mb-0">Persisted Feeding Fee classification only</p></div></div>
        @if($students->isEmpty())<div class="sv-empty"><i class="ri-user-search-line"></i>No student Feeding Fee bills match these filters.</div>@else
        <div class="sv-table-wrap"><table class="table bordered-table mb-0 sv-table"><thead><tr><th>Student ID</th><th>Student</th><th>Class</th><th>Academic period</th><th>Billed</th><th>Paid</th><th>Outstanding</th><th>Status</th></tr></thead><tbody>
            @foreach($students as $row)<tr><td>{{$row['student_id']}}</td><td class="fw-semibold">{{$row['student_name']}}</td><td>{{$row['class']}}</td><td>{{$row['academic_period']}}</td><td class="sv-money">{{$money($row['billed'])}}</td><td class="sv-money sv-credit">{{$money($row['paid'])}}</td><td class="sv-money sv-debit">{{$money($row['outstanding'])}}</td><td><span class="ac-pill {{$row['status']==='Paid'?'ac-pill-teal':($row['status']==='Partial'?'ac-pill-amber':'ac-pill-slate')}}">{{$row['status']}}</span></td></tr>@endforeach
        </tbody></table></div><div class="p-20">{{$students->links()}}</div>@endif
    </div>

    <div class="sv-card"><div class="sv-card-head"><div><h6>Confirmed collection transactions</h6><p class="text-sm text-secondary-light mb-0">Only completed BillPayment records; pending and rejected instruments are excluded</p></div></div>
        @if($payments->isEmpty())<div class="sv-empty"><i class="ri-hand-coin-line"></i>No confirmed payments match these filters.</div>@else
        <div class="sv-table-wrap"><table class="table bordered-table mb-0 sv-table"><thead><tr><th>Receipt</th><th>Payment date</th><th>Student</th><th>Academic period</th><th>Method</th><th>Amount</th><th>Vault posting</th></tr></thead><tbody>
            @foreach($payments as $row)<tr><td>@if($canReceipts)<a href="{{route('bill-payment-receipt',$row['id'])}}" class="sv-ref text-primary-600">{{$row['receipt']}}</a>@else<span class="sv-ref">{{$row['receipt']}}</span>@endif</td><td>{{$row['date']?->format('d M Y H:i')}}</td><td>{{$row['student_name']}}<div class="text-sm text-secondary-light">{{$row['student_id']}}</div></td><td>{{$row['academic_period']}}</td><td>{{$row['method']}}</td><td class="sv-money sv-credit">{{$money($row['amount'])}}</td><td>@if($row['posted'])<span class="ac-pill ac-pill-teal">Posted · {{$row['vault_reference']}}</span>@else<span class="ac-pill ac-pill-rose">Missing Vault credit</span>@endif</td></tr>@endforeach
        </tbody></table></div><div class="p-20">{{$payments->links()}}</div>@endif
    </div>
</div>
@endsection
