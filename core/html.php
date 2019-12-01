<?php

class HtmlHelper
{
	public static $_Title;
	public static $_ViewData;

	function __construct()
	{
		$this->m_title = "";
		HtmlHelper::$_ViewData = array();
		//Start an output buffer for html to be written to.
		ob_start();
	}

	//Helper to create a dropdown list for an array, with the ability to specify value and text fields and html classes
	public function DropDownList(array $model, array $htmlAttributes = null, string $value = 'id', string $text = 'value', string $selected = null)
	{
		if(count($model) > 0)
		{
			//Make sure the provided text and value fields exisit for the object type
			$propNames = get_object_vars($model[0]);
			if(!array_key_exists($value, $propNames) && $value !== null)
			{
				throw new InvalidArgumentException('$value argument ' . $value . ' does not correspond with a field in the provided model');
			}

			if(!array_key_exists($text, $propNames))
			{
				throw new InvalidArgumentException('$text argument ' . $text . ' does not correspond with a field in the provided model');
			}

			echo '<select '. $this->GetHtmlAttribStr($htmlAttributes) .'>';
			for($index = 0; $index < count($model); $index++)
			{
				$selectedAttrib = $selected != null ? ($model[$index]->$value == $selected ? ' selected="selected"' : '') : '';
				echo '<option value="' . ($value != null ? $model[$index]->$value : $model[$index]->$text) . '"' . $selectedAttrib . '>' . $model[$index]->$text . '</option>';
			}
			echo '</select>';
		}
		else
		{
			echo '<select '. $this->GetHtmlAttribStr($htmlAttributes) . ' />';
		}
	}

	public function Input($model, array $htmlAttributes = null, $type = "text")
	{
		if($htmlAttributes != null && array_key_exists('type', $htmlAttributes))
		{
			echo '<input ' . $this->GetHtmlAttribStr($htmlAttributes) . ' value="' . $model . '" />';
		}
		else
		{
			echo '<input type="'. $type .'" ' . $this->GetHtmlAttribStr($htmlAttributes) . ' value="' . $model . '" />';
		}
	}

	//Renders the buffered output, configurable as to whether to use layout file, and which file to use.
	public function Render(bool $useLayout = true, string $layoutFile = "_layout.php", string $layoutDir = __DIR__ . "/../Views/")
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' )
		{
			$useLayout = false;
		}
		
		if(isset(HtmlHelper::$_Title))
		{
			HtmlHelper::$_ViewData['Title'] = HtmlHelper::$_Title;
		}
		else
		{
			HtmlHelper::$_ViewData['Title'] = '';
		}

		HtmlHelper::$_ViewData['Body'] = ob_get_clean();

		if($useLayout === true && $layoutFile != null)
		{
			if(file_exists($layoutDir . $layoutFile))
			{
				ob_start();
				include $layoutDir . $layoutFile;
				echo ob_get_clean();
			}
		}
		else
		{
			echo HtmlHelper::$_ViewData['Body'];
		}

	}

	private function GetHtmlAttribStr(array $htmlAttributes)
	{
		if($htmlAttributes == null) return "";

		$htmlAttribStr = "";

		if(!array_key_exists("name", $htmlAttributes) && array_key_exists("id", $htmlAttributes))
		{
			//Copies ID to name if name is empty
			$htmlAttributes["name"] = $htmlAttributes["id"];
		}

		foreach($htmlAttributes as $name => $value)
		{
			if($value != "")
			{
				$htmlAttribStr .= $name . '="' . $value . '" '; 
			}
			else
			{
				$htmlAttribStr .= $name . " ";
			}
		}

		return $htmlAttribStr;
	}
}

$HTML = new HtmlHelper();

//Push the helper to the globals array, in case, for some reason, it isn't accessible.
$GLOBALS["HTML"] = $HTML;
?>