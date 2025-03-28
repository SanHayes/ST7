<?php


namespace App\Console\Commands;

use App\Models\Users;
use App\Models\UsersWallet;
use Illuminate\Console\Command;
class UpdatePrivate extends Command
{
	protected $signature = "update_private";
	protected $description = "更新私钥以及钱包地址";
	public function handle()
	{
		$this->comment("start1");
		foreach (Users::cursor() as $user) {
			echo $user->id . '--';
			$n = 0;
			$return = $this->updateWallet($user);
			while (!$return && $n < 3) {
				$n++;
				$return = $this->updateWallet($user);
			}
		}
		$this->comment("end");
	}
	public function updateWallet($user)
	{
		$address_url = '/v3/wallet/address';
		$project_name = config('app.name');
		$http_client = app('LbxChainServer');
		$response = $http_client->post($address_url, ['form_params' => ['userid' => $user->id, 'projectname' => $project_name]]);
		$result = json_decode($response->getBody()->getContents());
		if (!isset($result->code) || $result->code != 0) {
			$this->error('请求钱包接口发生异常');
			return false;
		}
		$address = $result->data;
		$wallets = UsersWallet::where('user_id', $user->id)->get();
		foreach ($wallets as $wallet) {
			if (empty($wallet->currencyCoin)) {
				continue 1;
			}
			$currency_type = $wallet->currencyCoin->type;
			if ($address) {
				if ($currency_type == 'btc') {
					$wallet->address = $address->btc_address;
					$wallet->private = $address->btc_private;
				} elseif ($currency_type == 'usdt') {
					$wallet->address = $address->usdt_address;
					$wallet->private = $address->usdt_private;
				} elseif ($currency_type == 'eth') {
					$wallet->address = $address->eth_address;
					$wallet->private = $address->eth_private;
				} elseif ($currency_type == 'erc20') {
					$wallet->address = $address->erc20_address;
					$wallet->private = $address->erc20_private;
				} elseif ($currency_type == 'xrp') {
					$wallet->address = $address->xrp_address;
					$wallet->private = $address->xrp_private;
				}
				$wallet->save();
				$this->comment("user_id:" . $wallet->user_id . ',' . $currency_type . '钱包私钥更新成功');
			}
		}
		$this->comment("user_id:" . $wallet->user_id . '用户私钥更新成功！');
		return true;
	}
}
