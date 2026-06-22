@extends('layouts.dashboard')
@section('title', 'Groups')
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
                                <h4>Groups</h4>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area">
                        @include('inc.messages')
                        <table id="html5-extension" class="table table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Owner</th>
                                    <th>Members</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups as $group)
                                    <tr class="{{ $group->is_pinned ? 'table-warning' : '' }}">
                                        <td>
                                            @if ($group->is_pinned)
                                                <span class="badge badge-warning"
                                                    title="Pinned to position #{{ $group->pin_position }}">
                                                    &#9733; #{{ $group->pin_position }}
                                                </span>
                                            @else
                                                {{ $loop->iteration }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ $group->title ?? 'N/A' }}
                                            @if ($group->is_pinned)
                                                <span class="badge badge-warning ms-1">TOP</span>
                                            @endif
                                        </td>
                                        <td>{{ $group->user->name ?? 'N/A' }}</td>
                                        <td>{{ $group->group_subscriptions->count() }}</td>
                                        <td>
                                            <span class="badge badge-light-info">
                                                {{ $group->group_status->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $group->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <form action="{{ route('groups.toggle-pin', $group->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('{{ $group->is_pinned ? 'Remove this group from top position?' : 'Pin this group to a top position? (max 2 allowed)' }}');">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-sm {{ $group->is_pinned ? 'btn-warning' : 'btn-outline-warning' }}"
                                                    title="{{ $group->is_pinned ? 'Unpin from top' : 'Pin to top (Priority)' }}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                        viewBox="0 0 24 24"
                                                        fill="{{ $group->is_pinned ? 'currentColor' : 'none' }}"
                                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <polygon
                                                            points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                                                        </polygon>
                                                    </svg>
                                                    {{ $group->is_pinned ? 'Unpin' : 'Top' }}
                                                </button>
                                            </form>
                                            <a href="{{ route('groups.show', $group->id) }}"
                                                class="btn btn-info btn-sm">View</a>
                                            @if (checkButtonPermission('groups', 'edit'))
                                                <a href="{{ route('groups.edit', $group->id) }}"
                                                    class="btn btn-warning btn-sm">Edit</a>
                                            @endif
                                            @if (checkButtonPermission('groups', 'delete'))
                                                <form action="{{ route('groups.destroy', $group->id) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Delete this group?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
