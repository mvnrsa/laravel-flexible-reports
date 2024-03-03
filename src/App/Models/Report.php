<?php
namespace mvnrsa\FlexibleReports\App\Models;

use Str;
use Carbon\Carbon;
use \DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;
use App\Models\Role;
use App\Models\Team;

class Report extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'flexible_reports';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'name',
        'query',
        'columns',
        'parameters',
        'created_at',
        'updated_at',
        'deleted_at',
        'team_id',
        'charts',
        'pre_function',
        'post_function',
    ];

	public function get_charts()
	{
		if (empty($this->charts))
			return [];

		$charts= array();
		$rows = explode("\n",$this->charts);
		foreach ($rows as $pos => $row)
		{
			$cols = explode(",",trim($row));

			$position = trim($cols[0] ?? "top");

			$charts[$position][$pos]['title']		= trim($cols[1] ?? "");
			$charts[$position][$pos]['type']		= trim($cols[2] ?? "");
			$charts[$position][$pos]['value_col']	= trim($cols[3] ?? "");
			$charts[$position][$pos]['group1_col']	= trim($cols[4] ?? "");
			$charts[$position][$pos]['group2_col']	= trim($cols[5] ?? "");
			$charts[$position][$pos]['limit']		= trim($cols[6] ?? "");
			$charts[$position][$pos]['function']	= trim($cols[7] ?? "sum");
		}

		return $charts;
	}

	public function get_columns()
	{
		if (empty($this->columns))
			return [];

		$columns = array();
		$rows = explode("\n",$this->columns);
		foreach ($rows as $pos => $row)
		{
			$cols = explode(",",trim($row));

			$name = trim($cols[0]);
			$columns[$name] = array();

			if (strpos($cols[1],'@')>0)
			{
				$columns[$name]['type'] = 'number';
				$columns[$name]['formatValue'] = trim($cols[1]);
			}
			else
				$columns[$name]['type'] = trim($cols[1]);

			$columns[$name]['label'] = trim($cols[2]);
			if (!empty($cols[3]))
			{
				$columns[$name]['footer'] = trim($cols[3]);
				$columns[$name]['footerText'] = "<b>@value";
			}
			if (!empty($cols[4]))
				$columns[$name]['cssStyle'] = trim($cols[4]);
		}

		return $columns;
	}

	public function get_parameters()
	{
		if (empty($this->parameters))
			return [];

		$parameters = array();
		$rows = explode("\n",$this->parameters);
		foreach ($rows as $row)
		{
			$cols = explode(",",trim($row));
			$type = strtolower(trim($cols[1]));
			$method = "get_$type";
			if (method_exists($this,$method))
				$parameters[] = $this->$method($cols);
			else
				$parameters[] = $this->get_text($cols);
		}

		return $parameters;
	}

	private function get_text($cols)
	{
		return [ 'name'  => trim($cols[0]), 'type'  => 'text', 'label' => trim($cols[2]), 'default' => trim($cols[3] ?? ""), ];
	}

	private function get_select($cols)
	{
		$retval = [	'name'  => trim($cols[0]), 'type'  => 'select', 'label' => trim($cols[2]), 'default' => trim($cols[3] ?? "") ];
		array_shift($cols);
		array_shift($cols);
		array_shift($cols);
		array_shift($cols);
		$retval["options"] = [ "" => trans('global.pleaseSelect') ];
		foreach ($cols as $option)
			$retval['options'][$option] = ucWords(strtolower($option));

		return $retval;
	}

	private function get_model($cols)
	{
		$retval = [	'name'  => trim($cols[0]), 'type'  => 'select', 'label' => trim($cols[2]), 'default' => trim($cols[3] ?? "") ];

		try
		{
			$cols2 = explode(":",$cols[4]);
			$model = "\\App\\Models\\" . trim($cols2[0]);
			$retval['options'] = $model::pluck($cols2[1], $cols2[2])
											->prepend(trans('global.pleaseSelect'),"")
											->toArray();
		}
		catch (Exception $e)
		{
			$retval['options'] = [];
		}

		return $retval;
	}

	private function get_date($cols)
	{
		return [ 'name'  => trim($cols[0]), 'type'  => 'date', 'label' => trim($cols[2]), 'default' => $this->get_date_default(trim($cols[3] ?? "")), ];
	}

	private function get_date_default($date)
	{
		$key = Str::snake($date);
		$format = 'Y-m-d';

		$dates = [
					'yesterday' => new Carbon('yesterday'),
					'today' => new Carbon('today'),
					'tomorrow' => new Carbon('tomorrow'),
					'first_of_this_month' => new Carbon('first day of this month'),
					'last_of_this_month'  => new Carbon('last day of this month'),
					'first_of_last_month' => new Carbon('first day of last month'),
					'last_of_last_month'  => new Carbon('last day of last month'),
					'first_of_this_year'  => Carbon::now()->startOfYear(),
					'last_of_this_year'   => Carbon::now()->endOfYear(),
					'first_of_last_year'  => Carbon::now()->subYear(1)->startOfYear(),
					'last_of_last_year'   => Carbon::now()->subYear(1)->endOfYear(),
				];

		$date = $dates[$key]->format($format) ?? "";

		return $date;
	}

	public function gate()
	{
		return $this->roles()->pluck('id')
					->intersect(Auth::user()->roles()->pluck('id'))
					->isNotEmpty();
	}

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'flexible_report_role');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
