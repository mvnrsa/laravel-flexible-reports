@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        <div class="row">
            {{ trans('flexibleReports::reports.edit') }} {{ trans('flexibleReports::reports.title_singular') }}
            <a class="btn-sm align-self-end ml-auto btn btn-default " href="{{ route('admin.reports.index') }}">
                <i class="fas fa-arrow-left fa-sm"></i> {{ trans('flexibleReports::reports.back_to_list') }}
            </a>
        </div>

    </div>

    <div class="card-body">
        <form method="POST" action="{{ route("admin.reports.update", [$report->id]) }}" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="form-group">
                <label class="required" for="name">{{ trans('flexibleReports::reports.fields.name') }}</label>
                <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" type="text" name="name" id="name" value="{{ old('name', $report->name) }}" required>
                @if($errors->has('name'))
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                @endif
                <span class="help-block">{{ trans('flexibleReports::reports.fields.name_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="query">{{ trans('flexibleReports::reports.fields.query') }}</label>
                <textarea class="form-control {{ $errors->has('query') ? 'is-invalid' : '' }}" name="query" id="query">{{ old('query', $report->query) }}</textarea>
                @if($errors->has('query'))
                    <span class="text-danger">{{ $errors->first('query') }}</span>
                @endif
                <span class="help-block">{{ trans('flexibleReports::reports.fields.query_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="columns">{{ trans('flexibleReports::reports.fields.columns') }}</label>
                <textarea class="form-control {{ $errors->has('columns') ? 'is-invalid' : '' }}" name="columns" id="columns">{{ old('columns', $report->columns) }}</textarea>
                @if($errors->has('columns'))
                    <span class="text-danger">{{ $errors->first('columns') }}</span>
                @endif
                <span class="help-block">{{ trans('flexibleReports::reports.fields.columns_helper') }}</span>
            </div>
            <div class="form-group">
                <label for="parameters">{{ trans('flexibleReports::reports.fields.parameters') }}</label>
                <textarea class="form-control {{ $errors->has('parameters') ? 'is-invalid' : '' }}" name="parameters" id="parameters">{{ old('parameters', $report->parameters) }}</textarea>
                @if($errors->has('parameters'))
                    <span class="text-danger">{{ $errors->first('parameters') }}</span>
                @endif
                <span class="help-block">{{ trans('flexibleReports::reports.fields.parameters_helper') }}</span>
            </div>


            <div class="form-group">
                <label for="charts">{{ trans('flexibleReports::reports.fields.charts') }}</label>
                <textarea class="form-control {{ $errors->has('charts') ? 'is-invalid' : '' }}" name="charts" id="charts">{{ old('charts', $report->charts) }}</textarea>
                @if($errors->has('charts'))
                    <span class="text-danger">{{ $errors->first('charts') }}</span>
                @endif
                <span class="help-block">{{ trans('flexibleReports::reports.fields.charts_helper') }}</span>
            </div>


            <div class="form-group">
                <label for="roles">{{ trans('flexibleReports::reports.fields.roles') }}</label>
                <div style="padding-bottom: 4px">
                    <span class="btn btn-info btn-xs select-all" style="border-radius: 0">{{ trans('flexibleReports::reports.select_all') }}</span>
                    <span class="btn btn-info btn-xs deselect-all" style="border-radius: 0">{{ trans('flexibleReports::reports.deselect_all') }}</span>
                </div>
                <select class="form-control select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}" name="roles[]" id="roles" multiple>
                    @foreach($roles as $id => $role)
                        <option value="{{ $id }}" {{ (in_array($id, old('roles', [])) || $report->roles->contains($id)) ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
                @if($errors->has('roles'))
                    <span class="text-danger">{{ $errors->first('roles') }}</span>
                @endif
                <span class="help-block">{{ trans('flexibleReports::reports.fields.roles_helper') }}</span>
            </div>
            <div class="form-group">
                <button class="btn btn-danger" type="submit">
                    {{ trans('flexibleReports::reports.save') }}
                </button>
            </div>
        </form>
    </div>
</div>



@endsection
