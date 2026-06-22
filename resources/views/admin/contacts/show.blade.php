@extends('layouts.dashboard')
@section('title', 'Contact Details')
@section('main-content')
<div class="middle-content container-xxl p-0">
    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-8 col-md-10 col-sm-12 col-12 layout-spacing mx-auto">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12 d-flex justify-content-between align-items-center">
                            <h4>Contact Message</h4>
                            <a href="{{ route('contacts.index') }}" class="btn btn-secondary btn-sm">Back</a>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    <table class="table table-bordered">
                        <tr><th>Name</th><td>{{ $contact->name ?? 'N/A' }}</td></tr>
                        <tr><th>Email</th><td>{{ $contact->email ?? 'N/A' }}</td></tr>
                        <tr><th>Phone</th><td>{{ $contact->phone ?? '-' }}</td></tr>
                        <tr><th>Subject</th><td>{{ $contact->subject ?? 'N/A' }}</td></tr>
                        <tr><th>Message</th><td>{{ $contact->message ?? 'N/A' }}</td></tr>
                        <tr><th>Date</th><td>{{ $contact->created_at->format('M d, Y H:i') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
