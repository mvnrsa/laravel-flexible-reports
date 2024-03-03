@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('flexibleReports::reports.show') }} {{ trans('flexibleReports::reports.title') }}
    </div>

    <div class="card-body">
        <div class="form-group">
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.reports.index') }}">
                    {{ trans('flexibleReports::reports.back_to_list') }}
                </a>
                <a class="btn btn-danger" href="{{ route('admin.reports.edit', $report->id) }}">
                    <i class="fas fa-pencil fa-sm"></i> {{ trans('flexibleReports::reports.edit') }}
                </a>
                <a class="btn btn-success" href="{{ route('admin.reports.form', $report->id) }}" target="_blank">
                    <i class="fas fa-flag-checkered"></i> {{ trans('flexibleReports::reports.run_report') }}
                </a>
            </div>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>
                            {{ trans('flexibleReports::reports.fields.id') }}
                        </th>
                        <td>
                            {{ $report->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('flexibleReports::reports.fields.name') }}
                        </th>
                        <td>
                            {{ $report->name }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('flexibleReports::reports.fields.query') }}
                        </th>
                        <td>
                            {{ $report->query }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('flexibleReports::reports.fields.columns') }}
                        </th>
                        <td>
                            {{ $report->columns }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('flexibleReports::reports.fields.parameters') }}
                        </th>
                        <td>
                            {{ $report->parameters }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('flexibleReports::reports.fields.charts') }}
                        </th>
                        <td>
                            {{ $report->charts}}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            {{ trans('flexibleReports::reports.fields.roles') }}
                        </th>
                        <td>
                            @foreach($report->roles as $key => $roles)
                                <span class="label label-info">{{ $roles->title }}</span>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="form-group">
                <a class="btn btn-default" href="{{ route('admin.reports.index') }}">
                    {{ trans('flexibleReports::reports.back_to_list') }}
                </a>
            </div>
        </div>
    </div>
</div>



@endsection
