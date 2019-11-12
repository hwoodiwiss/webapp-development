<?php

include_once 'utils.php';

class DBData
{
	public function __construct(array $dbObject = null)
	{
		if($dbObject != null)
		{
			$props = get_object_vars($this);
			foreach($props as $key => $value)
			{
				$this->$key = SafeGetValue($dbObject, $key);
			}
		}
	}

	public function GetPartial(array $propNames)
	{
		$props = get_object_vars($this);
		$output = array();
		foreach($propNames as $index => $value)
		{
			$propVal = SafeGetValue($props, $value);
			if($propVal != null)
			{
				$output[$value] = $propVal;
			}
		}

		return $output;
	}

}

?>