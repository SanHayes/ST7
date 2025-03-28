<?php

namespace App\Console\Commands;

use App\Models\AccountLog;
use App\Models\Setting;
use App\Models\Users;
use App\Models\HistoricalData;
use App\Utils\RPC;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
class HistoricalDatas extends Command
{
	protected $signature = "historical_data";
	protected $description = "历史数据";
	public function handle()
	{
		DB::beginTransaction();
		try {
			$day = intval(date("d", time()));
			$week = date("w");
			$yesterday_start = date("Y-m-d", strtotime("-1 day"));
			$yesterday_start = strtotime($yesterday_start);
			$yesterday_end = $yesterday_start + 86400;
			$aaa = HistoricalData::insertData($yesterday_start, $yesterday_end);
			if ($week == "1") {
				$week_start = date("Y-m-d", strtotime("last Monday"));
				$week_start = strtotime($week_start);
				HistoricalData::insertData($week_start, time(), "week");
			}
			if ($day == 1) {
				$month_start = date("Y-m-d", strtotime("last month"));
				$month_start = strtotime($month_start);
				HistoricalData::insertData($month_start, time(), "month");
			}
			DB::commit();
		} catch (\Exception $ex) {
			DB::rollback();
			$this->comment($ex->getMessage());
		}
	}
}
