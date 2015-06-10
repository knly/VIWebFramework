<?php

class VIForm
{
	private $form_wrapper = '';
	private $form_items = Array();

	function __construct($action, $method, $options = '')
	{
		if(strtolower($method) != 'post' && strtolower($method) != 'get')
		{
			throw new BadFunctionCallException("Invalid method: " . strtolower($method));
			__destruct();
		}

		$this->form_wrapper = '<form action="' . $action . '" method="' . $method . '" ' . $options . '>';
	}

	function __destruct()
	{

	}

	function add( $tag, $type = '', $name = '', $value = '', $content = '', $options = Array(), $break = true )
	{
		if(!isset($tag))
		{
			throw new BadFunctionCallException("You need at least a tag!");
			return;
		}

		$item = new VIFormItem();
		$item->tag = $tag;
		$item->type = $type;
		$item->name = $name;
		$item->value = $value;
		$item->content = $content;
		if(strtolower($type) == 'hidden')
			$item->break = false;
		else
			$item->break = $break;
		$item->options = $options;

		array_push($this->form_items, $item);
	}

	function headl( $level, $content )
	{
		$item = new VIFormItem();
		$item->tag = 'h' . (int)$level;
		$item->content = $content;

		array_push($this->form_items, $item);
	}

	function draw( $submit = true )
	{
		echo $this->form_wrapper;
		foreach($this->form_items as $f)
		{
			$string = '<' . $f->tag;
			if(!empty($f->type))
				$string .= ' type="' . $f->type . '"';
			if(!empty($f->name))
				$string .= ' name="' . $f->name . '"';
			if(!empty($f->value))
				$string .= ' value="' . $f->value . '"';
			if(!empty($f->options))
			{
				foreach($f->options as $o)
				{
					$string .= ' ' . $o;
				}
			}
		
			$string .= '>' . $f->content . '</' . $f->tag . '>';
		
			if($f->break)
				$string .= '<br>';

			echo $string;
		}
		if($submit === true)
		{
			echo '<input type="submit" />';
		}
		echo '</form>';
	}

	function postSet()
	{

	}

	function getSet()
	{

	}
}

class VIFormItem
{
	public $tag, $type, $name, $value, $content, $break, $options;
}

?>