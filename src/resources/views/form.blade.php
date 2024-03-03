@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('flexibleReports::reports.run_report') }}: {{ $report->name }}
        <a href='{{ route("admin.reports.index") }}' class='btn btn-default mx-5'>
			{{ trans('flexibleReports::reports.back_to_list') }}
		</a>
    </div>

    <div class="card-body">
        <div class="form-group">
			<form id='reportForm' method='POST' action='{{ route("admin.reports.run",["report"=>$report->id, "format"=>"html"]) }}' target="_blank" data-pdf='{{ route("admin.reports.run",["report"=>$report->id, "format"=>"pdf"]) }}' data-xls='{{ route("admin.reports.run",["report"=>$report->id, "format"=>"xls"]) }}' data-csv='{{ route("admin.reports.run",["report"=>$report->id, "format"=>"xls"]) }}' data-html='{{ route("admin.reports.run",["report"=>$report->id, "format"=>"html"]) }}' data-ods='{{ route("admin.reports.run",["report"=>$report->id, "format"=>"ods"]) }}'>
			@csrf
	            <table class="table table-bordered table-striped">
	                <tbody>
						@foreach ($report->get_parameters() as $pos => $param)
							<tr>
								<th width='25%' class='text-right'>{{ $param['label'] }}:</th>
								<td>

									@if($param['type'] == "text")
										<input name="{{ $param['name'] }}" value="{{ $param['default'] }}" class="form-control form-control-sm" />
									@endif

									@if($param['type'] == "date")
										<input name="{{ $param['name'] }}" type="date" value="{{ $param['default'] }}" class="form-control form-control-sm" />
									@endif

									@if($param['type'] == "select")
										<select name="{{ $param['name'] }}" class="form-control form-control-sm" />
											@foreach ($param['options'] as $key => $value)
												@if ($key == $param['default'])
													<option value='{{ $key }}' selected>{{ $value }}</option>
												@else
													<option value='{{ $key }}'>{{ $value }}</option>
												@endif
											@endforeach
										</select>
									@endif
								</td>
							</tr>
						@endforeach
						<tr>
							<td>&nbsp;</td>
							<td>
								<button type="submit" title='Web' onclick='setFormat("html");' class="btn btn-outline-info px-4"><i class='fal fa-file-code'></i></button>
								<button type="submit" title='PDF' onclick='setFormat("pdf");' class="btn btn-outline-info px-4"><i class='fal fa-file-pdf'></i></button>
								<button type="submit" title='Excel' onclick='setFormat("xls");' class="btn btn-outline-info px-4"><i class='fal fa-file-excel'></i></button>
								<button type="submit" title='ODS' onclick='setFormat("ods");' class="btn btn-outline-info px-4"><i class='fal fa-file-spreadsheet'></i></button>
								<button type="submit" title='CSV' onclick='setFormat("csv");' class="btn btn-outline-info px-4"><i class='fal fa-file-csv'></i></button>
							</td>
						</tr>
	                </tbody>
	            </table>
			</form>
        </div>
    </div>
</div>
@endsection

@section ('scripts')
<script>
	function setFormat(format)
	{
		var url = $('#reportForm').data(format);
		console.log(url);
		$('#reportForm').attr('action',url);
	}
</script>
@endsection
