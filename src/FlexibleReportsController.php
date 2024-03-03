<?php
namespace mvnrsa\FlexibleReports;

use DB;
use Str;
use Auth;
use App\Http\Controllers\Controller;
use mvnrsa\FlexibleReports\App\Http\Requests\MassDestroyReportRequest;
use mvnrsa\FlexibleReports\App\Http\Requests\StoreReportRequest;
use mvnrsa\FlexibleReports\App\Http\Requests\UpdateReportRequest;
use mvnrsa\FlexibleReports\App\Models\Report;
use App\Models\Role;
use App\Models\Team;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use mvnrsa\FlexibleReports\App\Utils\DataSetFactory;
use App\Utils\pdfUtils;
use \koolreport\processes\Group;
use \koolreport\processes\ColumnMeta;

class FlexibleReportsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('report_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
            $query = Report::with(['roles:title',])->select('id','name')->get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'report_show';
                $editGate = 'report_edit';
                $deleteGate = 'Xreport_delete';
                $crudRoutePart = 'reports';
				$extraButtons = array(array('label'=>'<i class="fal fa-flag-checkered"></i>', 'href'=>route('admin.reports.form',$row['id']), 'class'=>'btn-success'));

                return view('partials.datatablesActions', compact(
                'viewGate',
                'editGate',
                'deleteGate',
                'crudRoutePart',
				'extraButtons',
                'row'
            ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : '';
            });
            $table->editColumn('roles', function ($row) {
                $labels = [];
                foreach ($row->roles as $role) {
                    $labels[] = sprintf('<span class="label label-info label-many">%s</span>', $role->title);
                }

                return implode(' ', $labels);
            });

            $table->rawColumns(['actions', 'placeholder', 'roles']);

            return $table->make(true);
        }

        $roles = Role::pluck('title');
        // $teams = Team::get();

        return view('flexibleReports::index', compact('roles'));
    }

    public function create()
    {
        abort_if(Gate::denies('report_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::pluck('title', 'id');

        return view('flexibleReports::create', compact('roles'));
    }

    public function store(StoreReportRequest $request)
    {
        $report = Report::create($request->all());
        $report->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.reports.index');
    }

    public function edit(Report $report)
    {
        abort_if(Gate::denies('report_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::pluck('title', 'id');

        $report->load('roles'/*, 'team'*/);

        return view('flexibleReports::edit', compact('report', 'roles'));
    }

    public function update(UpdateReportRequest $request, Report $report)
    {
        $report->update($request->all());
        $report->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.reports.index');
    }

    public function show(Report $report)
    {
        abort_if(Gate::denies('report_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $report->load('roles'/*, 'team'*/);

        return view('flexibleReports::show', compact('report'));
    }

    public function destroy(Report $report)
    {
        abort_if(Gate::denies('report_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $report->delete();

        return back();
    }

    /* public function massDestroy(MassDestroyReportRequest $request)
    {
        Report::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }*/

	public function showForm($report)
	{
		$report = $this->getReport($report);

		abort_unless($report->gate(), 403);

        return view('flexibleReports::form', compact('report'));
	}

	public function run($report, string $format, Request $request)
	{
		$report = $this->getReport($report);

		abort_unless($report->gate(), 403);

		$params = [];
		foreach ($request->except('_token') as $param => $value)
			if (strpos($report->query,":$param") !== false)
				$params[$param] = $value ?? "";

		// This needs improvement, should go through prepare statement
		$query = $this->getSqlWithBindings($report->query, $params);

		// Labels for parameters
		$param_labels = collect($report->get_parameters())->pluck('label','name');

		// Create a datasource for the report
		$dsf = new DataSetFactory($query); // , $params);

		if ($format == "xls" || $format == "csv" || $format == "ods")
		{
			$types = ['xls'=>'application/vnd.ms-excel', 'csv'=>'text/csv', 'ods'=>'application/ods'];
			$methods = ['xls'=>'exportToExcel', 'csv'=>'exportToCSV', 'ods'=>'exportToODS'];

			$type   = $types[$format];
			$method = $methods[$format];

			$filename = ucfirst(Str::camel($report->name)) . "_" .
					\Carbon\Carbon::now()->format("Ymd_His") . ".$format";

			$tmpfile = storage_path("tmp/$filename");
			$dsf->run()->$method()->saveAs($tmpfile);
			$content = file_get_contents($tmpfile);
			unlink($tmpfile);

			return response($content)
						->header('Content-Type',$type)
						->header('Content-disposition',"attachment; filename=\"$filename\"");
		}
		elseif ($format == "html")
		{
			// Run the query and make the table for the report
			$table = \koolreport\widgets\koolphp\Table::html([	"dataSource"=>$dsf->dataSet("custom"),
																"columns" => $report->get_columns(),
																"showFooter" => true,
																"cssClass" => [
																	"table" => "table table-bordered table-striped table-sm",
																	],
																"paging" => [
																		"pageSize"=>20,
																		"pageIndex"=>0,
																	],
															]);

			$charts = $this->gen_charts($report, $dsf);

	        return view('flexibleReports::run', compact('report','table','charts','params','param_labels'));
		}
		elseif ($format == "pdf")
		{
			// Run the query and make the table for the report
			$table = \koolreport\widgets\koolphp\Table::html([	"dataSource"=>$dsf->dataSet("custom"),
																"columns" => $report->get_columns(),
																"showFooter"=>true,
															]);

			$charts = $this->gen_charts($report, $dsf);

	        $html = view('flexibleReports::run', compact('report','table','charts','params',"param_labels"));

			// Convert to PDF and send back to the browser
			$pdf_file = (new pdfUtils)->fromHtml($html);
			$contents = file_get_contents($pdf_file);
			unlink($pdf_file);

			$filename = ucfirst(Str::camel($report->name)) . "_" .
						\Carbon\Carbon::now()->format("Ymd_His") . ".pdf";
			return response($contents)
						->header('Content-Type', "application/pdf")
						->header("Content-disposition","attachment; filename=\"$filename\"");
		}

		abort(404,"Unsupported format: $format");
	}

	/* public function getSqlWithBindings($query)
	{
		return vsprintf(str_replace('?', '%s',
					$query->toSql()), collect($query->getBindings())->map(function ($binding) {
						return is_numeric($binding) ? $binding : "'{$binding}'";
					})->toArray());
	} */

	private function getSqlWithBindings($query, $bindings)
	{
		foreach ($bindings as $key => $value)
			$query = str_replace(":$key", "'$value'", $query);

		return $query;
	}

	private function gen_charts($report, $dsf)
	{
		$charts = array();
		$charts["top"] = array();
		$charts["bottom"] = array();

		foreach ($report->get_charts() as $pos => $group)
		{
			foreach ($group as $key => $chart)
			{
				$charts[$pos][$key] = $chart;
				$charts[$pos][$key]['html'] = "Testing " . $chart['title'];

				$class = "koolreport\widgets\google\\" . $chart["type"];
				if (!class_exists($class))
					$charts[$pos][$key]['html'] = "Class $class not found!";
				{
					$ds = "chart_$key";

					// This is *really* ugly - running the query multiple times! FIXME
					extract($chart);
					$table_query = $dsf->getQuery("custom");
					$func = (empty($function) ? "sum" : $function);
					$query = "SELECT $group1_col, ";
					if (!empty($group2_col))
						$query .= "$group2_col, ";
					$query .= " $func($value_col) `$func`\nFROM (\n\n$table_query\n\n) sub\nGROUP BY $group1_col";
					if (!empty($group2_col))
						$query .= ", $group2_col";
					$query .= "\nHAVING `$func` <> 0";		// 2023/03/30 - Eliminate 0 values in charts
					$query .= "\nORDER BY `$func` DESC";
					if (!empty($limit))
						$query .= "\nLIMIT $limit";

					$dsf->addDataSet($ds,$query);

					$columns = [ $group1_col,
									"$func"=>	[	"type" => "number", "label"=>Str::title($func), ],
									"$func" . "_annotation" =>
												[	"role" => "annotation",
													"formatValue"=>function($value, $row) use ($func)
																		{ return number_format($row["$func"],0); },
												],
							   ];

					/* $options = null;
					if (class_basename($class) == 'PieChart')
						$options = [ 'pieSliceText' => 'value' ]; */

					$charts[$pos][$key]['html'] = $class::html([
																	"dataSource"=>$dsf->dataSet($ds),
																 	"colorScheme"=>config('app.ChartsColourScheme'),
																	 "columns"=>$columns,
																	 // "options"=>$options,
																]);
				}
			}
		}

		return $charts;
	}

	/*
	 * Allow reports to be run via their names instead of being injected
	 */
	private function getReport($report)
	{
		if (is_object($report))
			return $report;
		elseif(is_numeric($report))
			$report = Report::find($report);
		else
			$report = Report::where('name',$report)->first();

		if (!$report)
			abort(404);

		return $report;
	}
}
