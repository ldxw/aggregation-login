<?php

if ($addons_config["path"] != "clogin") {
	error();
}
class clogin_plugin
{
	public $ide;
	public $addons_config;
	public $api_config;
	public $asset_data;
	public $action;
	public $status;
	public $bt_host;
	public $views;
	public $plugins_config;
	public function system($class, $value)
	{
		if ($class == "ide") {
			$this->ide = $value;
		}
		if ($class == "addons_config") {
			$this->addons_config = $value;
		}
		if ($class == "api_config") {
			$this->api_config = $value;
		}
		if ($class == "asset_data") {
			$this->asset_data = $value;
		}
		if ($class == "action") {
			$this->action = $value;
		}
		if ($class == "status") {
			$this->status = $value;
		}
		if ($class == "bt_host") {
			$this->bt_host = $value;
		}
		if ($class == "views") {
			$this->views = $value;
		}
		if ($class == "plugins_config") {
			$this->plugins_config = $value;
		}
	}
	public function plugins_config()
	{
		return $this->plugins_config;
	}
	public function views()
	{
		return $this->views;
	}
	public function bt_host()
	{
		return $this->bt_host;
	}
	public function status()
	{
		return $this->status;
	}
	public function ide()
	{
		return $this->ide;
	}
	public function addons_config()
	{
		return $this->addons_config;
	}
	public function api_config()
	{
		return $this->api_config;
	}
	public function asset_data()
	{
		return $this->asset_data;
	}
	public function action()
	{
		return $this->action;
	}
}