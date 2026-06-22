@extends('layouts.dashboard')

@section('title', 'Templates')

@section('head')
<link href="{{ asset('') }}src/plugins/src/table/datatable/datatables.css" rel="stylesheet" type="text/css">
<link href="{{ asset('') }}src/plugins/src/table/datatable/dt-global_style.css" rel="stylesheet" type="text/css">
@endsection

@section('main-content')
<div class="middle-content container-xxl p-0">


    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                            <h4>All Templates</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area">
                    @include('inc.messages')
                    <div class="table-responsive">
                        <table id="html5-extension" class="table table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Owner</th>
                                    <th>Status</th>
                                    <th>Rank Score</th>
                                    <th>Price</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $i => $template)
                                <tr class="{{ $template->is_pinned ? 'table-warning' : '' }}">
                                    <td>
                                        @if($template->is_pinned)
                                            <span class="badge badge-warning" title="Pinned to position #{{ $template->pin_position }}">
                                                &#9733; #{{ $template->pin_position }}
                                            </span>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        {{ $template->title }}
                                        @if($template->is_pinned)
                                            <span class="badge badge-warning ms-1">TOP</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->user->name ?? 'N/A' }}</td>
                                    <td><span class="badge badge-light-info">{{ $template->template_status->name ?? 'N/A' }}</span></td>
                                    <td>
                                        <span title="Rank score (higher = better organic rank)">{{ number_format($template->rank_score, 2) }}</span>
                                    </td>
                                    <td>{{ showMoney($template->price ?? 0) }}</td>
                                    <td>{{ $template->created_at->format('M d, Y') }}</td>
                                    <td>
                                        {{-- Pin / Unpin (Top Priority) button --}}
                                        <form action="{{ route('templates.toggle-pin', $template->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('{{ $template->is_pinned ? 'Remove this template from top position?' : 'Pin this template to a top position? (max 2 allowed)' }}');">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm {{ $template->is_pinned ? 'btn-warning' : 'btn-outline-warning' }}"
                                                title="{{ $template->is_pinned ? 'Unpin from top' : 'Pin to top (Priority)' }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="{{ $template->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                {{ $template->is_pinned ? 'Unpin' : 'Top' }}
                                            </button>
                                        </form>

                                        @if(checkButtonPermission('templates', 'show'))
                                        <a href="{{ route('templates.show', $template->id) }}" class="btn btn-info btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a>
                                        @endif
                                        @if(checkButtonPermission('templates', 'edit'))
                                        <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-warning btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a>
                                        @endif
                                        @if(checkButtonPermission('templates', 'delete'))
                                        <form action="{{ route('templates.destroy', $template->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');"> @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg></button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="8" class="text-center">No templates found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('') }}src/plugins/src/table/datatable/datatables.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/dataTables.buttons.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/jszip.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/buttons.html5.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/button-ext/buttons.print.min.js"></script>
<script src="{{ asset('') }}src/plugins/src/table/datatable/custom_miscellaneous.js"></script>
@endsection
