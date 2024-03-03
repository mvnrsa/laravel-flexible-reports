@extends('layouts.admin')
@section('content')
@can('report_create')
    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.reports.create') }}">
                {{ trans('flexibleReports::reports.add') }} {{ trans('flexibleReports::reports.title_singular') }}
            </a>
        </div>
    </div>
@endcan
<div class="card">
    <div class="card-header">
        {{ trans('flexibleReports::reports.title_singular') }} {{ trans('flexibleReports::reports.list') }}
    </div>

    <div class="card-body">
        <table class="indexDataTable compact table table-bordered table-striped table-hover ajaxTable datatable datatable-Report">
            <thead>
                <tr>
                    <th width="10">

                    </th>
                    <th>
                        {{ trans('flexibleReports::reports.fields.id') }}
                    </th>
                    <th>
                        {{ trans('flexibleReports::reports.fields.name') }}
                    </th>
                    <th>
                        {{ trans('flexibleReports::reports.fields.roles') }}
                    </th>
                    <th>
                        &nbsp;
                    </th>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <input class="search form-control form-control-sm" type="text" placeholder="{{ trans('flexibleReports::reports.search') }}">
                    </td>
                    <td>
                        <input class="search form-control form-control-sm" type="text" placeholder="{{ trans('flexibleReports::reports.search') }}">
                    </td>
                    <td>
                        <!--select class="search form-control form-control-sm">
                            <option value>{{ trans('flexibleReports::reports.all') }}</option>
                            @foreach($roles as $key => $title)
                                <option value="{{ $title }}">{{ $title }}</option>
                            @endforeach
                        </select-->
                    </td>
                    <td>
                    </td>
                </tr>
            </thead>
        </table>
    </div>
</div>



@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('report_delete')
  let deleteButtonTrans = '{{ trans('flexibleReports::reports.datatables.delete') }}';
  let deleteButton = {
    text: deleteButtonTrans,
    url: "{{ route('admin.reports.massDestroy') }}",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).data(), function (entry) {
          return entry.id
      });

      if (ids.length === 0) {
        alert('{{ trans('flexibleReports::reports.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('flexibleReports::reports.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  let dtOverrideGlobals = {
    buttons: dtButtons,
    processing: true,
    serverSide: true,
    retrieve: true,
    aaSorting: [],
    ajax: "{{ route('admin.reports.index') }}",
    stateSave: true,
    colReorder: true,
    rowId: 'id',
    columns: [
        { data: 'placeholder', name: 'placeholder' },
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name', className: "linkToShowPage" },
        { data: 'roles', name: 'roles.title' },
        { data: 'actions', name: '{{ trans('flexibleReports::reports.actions') }}' },
    ],
    orderCellsTop: true,
    order: [[ 2, 'asc' ]],
    pageLength: 50,
  };
  let table = $('.datatable-Report').DataTable(dtOverrideGlobals);
  $('a[data-toggle="tab"]').on('shown.bs.tab click', function(e){
      $($.fn.dataTable.tables(true)).DataTable()
          .columns.adjust();
  });

let visibleColumnsIndexes = null;
$('.datatable thead').on('input', '.search', function () {
      let strict = $(this).attr('strict') || false
      let value = strict && this.value ? "^" + this.value + "$" : this.value

      let index = $(this).parent().index()
      if (visibleColumnsIndexes !== null) {
        index = visibleColumnsIndexes[index]
      }

      table
        .column(index)
        .search(value, strict)
        .draw()
  });
table.on('column-visibility.dt', function(e, settings, column, state) {
      visibleColumnsIndexes = []
      table.columns(":visible").every(function(colIdx) {
          visibleColumnsIndexes.push(colIdx);
      });
  })
});

</script>
@endsection
