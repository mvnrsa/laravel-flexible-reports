<?php
namespace mvnrsa\FlexibleReports\App\Utils;

use \koolreport\excel\ExportHandler;

class DataSetFactory extends \koolreport\KoolReport
{
    use \koolreport\laravel\Friendship;
    use \koolreport\excel\ExcelExportable;
    use \koolreport\excel\CSVExportable;

	// The connection to use
	protected $connection = "mysql";

	// Queries for the datasets
	protected $queries = [
			"custom"		=> "SELECT curdate()",
			"users"			=> "SELECT * FROM users",
			"tran_summary"	=> "select date(created_at) Day, sum(if(brand_code='DC',incl_amount,0)) `dc_amount`, sum(if(brand_code='LD',incl_amount,0)) `ld_amount` from tran_header where deleted_at is null and created_at>(curdate() - interval 10 day) group by Day order by Day",
			// "dc_sample"		=> "select date(cart.date) d, count(distinct cart.id)*1000+rand()*1000 `Amount` from `dcsa_obiwan`.`cart` group by d order by d desc limit 7",
			"departments"	=> "select department_id, count(*) cnt from styles where department_id<>'NULL' group by department_id limit 6",
		];

	// Requested datasets
	protected $rds = [];

	// dataSources
	protected $dataSources = [];

	// sets of parameters
	protected $params = [];

	public function __construct($ds=null, $params=[], $connection=null)
	{
		if (empty($ds))	// Set up all datasets
			$this->rds = array_keys($this->queries);
		elseif (is_array($ds))	// Specific set of datasets
			$this->rds = $ds;
		elseif (is_string($ds) && stripos($ds,"SELECT") !== false)		// custom query
		{
			$this->rds     = [ 'custom' ];
			$this->queries = ['custom' => $ds];
			$this->params  = ['custom' => $params];
		}
		elseif (is_string($ds))// one dataset
			$this->rds = [$ds];

		// Run the friendship constructor directly
		$this->__constructFriendship();

		// And run the setup
		$this->setup();
	}

	public function setup()
	{
		foreach($this->rds as $ds)
		{
			$query  = $this->queries[$ds] ?? null;
			$params = $this->params[$ds] ?? [];

			if ($query)
				$this->src($this->connection)->query($query,$params)->pipe($this->dataStore($ds));
		}
	}

	public function addDataSet($ds, $query, $params=[])
	{
		$this->queries[$ds] = $query;

		if ($query)
			$this->src($this->connection)->query($query, $params)->pipe($this->dataStore($ds));

		return $this;
	}

	// Alias for dataStore	-- widgets use dataSource => $dsf->dataStore(...)
	public function dataSet($ds)
	{
		return $this->dataStore($ds);
	}

	// Allow calling src from outside
	/* public function srcExt($ds)
	{
		return $this->src($ds);
	}*/

	public function getQuery($ds)
	{
		return $this->queries[$ds] ?? "";
	}

    public function exportToODS($params = [], $exportOption = [])
    {
        return (new ExportHandler($this, $this->dataStores))
            ->exportToODS($params, $exportOption);
    }

	public function getData($ds)
	{
		return $this->dataStores[$ds]->data() ?? null;
	}

	public function toXls($ds, $filename=null)
	{
		if (empty($filename))
			$filename = $ds . ".xls";
		elseif (stripos($filename,".xls") === false)
			$filename .= ".xls";

		$retval = "";
		$data = $this->getData($ds);

		// To define column name in first row.
		$column_names = false;

		// Run loop through each row in $customers_data
		foreach ($data as $row)
		{
			if (!$column_names)
			{
				$retval .= implode("\t", array_keys($row)) . "\n";
				$column_names = true;
			}

			// The array_walk() function runs each array element in a user-defined function.
			// array_walk($row, $this->filterData());
			foreach ($row as $key => $value)
				$row[$key] = $this->filterData($value);
			$retval .= implode("\t", array_values($row)) . "\n";
		}

		return response($retval)
					->header('Content-Type','application/vnd.ms-excel')
					->header('Content-disposition',"attachment; filename=\"$filename\"");
	}

	private function filterData($str)
	{
		$str = preg_replace("/\t/", "\\t", $str);
		$str = preg_replace("/\r?\n/", "\\n", $str);
		if (strstr($str, '"'))
			$str = '"' . str_replace('"', '""', $str) . '"';

		return $str;
	}
}
