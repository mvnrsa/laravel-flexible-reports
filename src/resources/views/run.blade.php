<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>{{ $report->name }}</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/fa5-pro.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet" />
    <link href="{{ asset('css/adminlte.3_2_0.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/margins.css') }}" rel="stylesheet" />

    <!-- script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script -->
</head>

<body class="" style="height: auto;">
	<div class='container text-center'>
		<h1 class="mb-0">{{ $report->name }}</h1>
		<div class="row mt-0 mb-2">
			<div class='col-12 col-md-2 text-left'>
				<form method="POST" id="exportForm" action="#" class="hideOnPrint">
					@csrf
					@foreach ($params as $key => $value)
						<input type="hidden" name='{{ $key}}' value="{{ $value }}" />
					@endforeach
					<div class="btn-group" role="group">
						<button type="submit" title='PDF' class="btn btn-default btn-sm px-3" onclick="exportPdf();">
							<i class="fas fa-file-pdf"></i>
						</button>
						<button type="submit" title='Excel' class="btn btn-default btn-sm px-3" onclick="exportXls();">
							<i class="fas fa-file-excel"></i>
						</button>
						<button type="submit" title='ODS' class="btn btn-default btn-sm px-3" onclick="exportOds();">
							<i class="fas fa-file-spreadsheet"></i>
						</button>
						<button type="submit" title='CSV' class="btn btn-default btn-sm px-3" onclick="exportCsv();">
							<i class="fas fa-file-csv"></i>
						</button>
					</div>
				</form>
			</div>
			<div class='col-12 col-md-8 text-center'>
				<small>
					{{ trans('flexibleReports::reports.prepared_by') }}:
					{{ Auth::user()->name }} -
					{{ \Carbon\Carbon::now()->format(trans('flexibleReports::reports.report_date_format')) }}
				</small>
				@if (count($param_labels) > 0)
					<br />
					<small>
						@foreach ($param_labels as $key => $label)
							{{ $label ?? "" }}: {{ $params[$key] ?? "" }}
						@endforeach
					</small>
				@endif
			</div>
			<div class='col-12 col-md-2 text-left'>
			</div>
		</div>

		@if (!empty($charts["top"]))
			<div class="row mb-3">
				@foreach ($charts["top"] as $top_pos => $chart)
					@if ((count($charts["top"])%2) && $top_pos == 0)
						<div class='col-12'>
					@else
						<div class='col-6'>
					@endif
						<h4 class="mb-0">{{ $chart["title"] }}</h4>
						{!! $chart["html"] !!}
					</div>
				@endforeach
			</div>
		@endif

		<div>
			{!! $table !!}
		</div>

		@if (!empty($charts["bottom"]))
			<div class="row mb-3">
				@foreach ($charts["bottom"] as $bottom_pos => $chart)
					@if ((count($charts["bottom"])%2) && ($bottom_pos-$top_pos-1) == 0)
						<div class='col-12'>
					@else
						<div class='col-6'>
					@endif
						<h4 class="mb-0">{{ $chart["title"] }}</h4>
						{!! $chart["html"] !!}
					</div>
				@endforeach
			</div>
		@endif

	</div>
<script>

function exportPdf()
{
	var url = "{{ route('admin.reports.run',['report' => $report->id, 'format'=>'pdf' ]) }}";
	document.getElementById('exportForm').action = url;
}

function exportXls()
{
	var url = "{{ route('admin.reports.run',['report' => $report->id, 'format'=>'xls' ]) }}";
	document.getElementById('exportForm').action = url;
}

function exportCsv()
{
	var url = "{{ route('admin.reports.run',['report' => $report->id, 'format'=>'csv' ]) }}";
	document.getElementById('exportForm').action = url;
}

function exportOds()
{
	var url = "{{ route('admin.reports.run',['report' => $report->id, 'format'=>'ods' ]) }}";
	document.getElementById('exportForm').action = url;
}
</script>
</body>
</html>
