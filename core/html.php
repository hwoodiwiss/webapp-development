<?php

require_once 'utils.php';

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
	public function DropDownListFor(array $model, string $id, string $class, string $value = 'id', string $text = 'value', string $selected = null)
	{
		if(count($model) > 0)
		{
			//Make sure the provided text and value fields exisit for the object type
			$propNames = get_object_vars($model[0]);
			if(SafeGetValue($propNames, $value) == null && $value !== null)
			{
				throw new InvalidArgumentException('$value argument ' . $value . ' does not correspond with a field in the provided model');
			}

			if(SafeGetValue($propNames, $text) == null)
			{
				throw new InvalidArgumentException('$text argument ' . $text . ' does not correspond with a field in the provided model');
			}
			
			echo '<select id="' . $id . '" class="' . $class . '">';
			for($index = 0; $index < count($model); $index++)
			{
				$selectedAttrib = $selected != null ? ($model[$index]->$value == $selected ? ' selected="selected"' : '') : '';
				echo '<option value="' . ($value != null ? $model[$index]->$value : $model[$index]->$text) . '"' . $selectedAttrib . '>' . $model[$index]->$text . '</option>';
			}
			echo '</select>';
		}
		else
		{
			echo '<select id="' . $id . '" name="' . $id . '" class="' . $class . '" />';
		}
	}

	public function TextBoxFor(string $model)
	{
		
	}

	//Renders the buffered output, configurable as to whether to use layout file, and which file to use.
	public function Render(bool $useLayout = true, string $layoutFile = "_layout.php", string $layoutDir = __DIR__ . "\\..\\Views\\")
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

		HtmlHelper::$_ViewData['BodyContent'] = ob_get_clean();

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
			echo HtmlHelper::$_ViewData['BodyContent'];
		}

	}
}

$HTML = new HtmlHelper();

?>