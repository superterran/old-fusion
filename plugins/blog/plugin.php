<?php

class blog extends fusion
{

	function init()
	{
		
		$this->loader();
		
	}

	function render()
	{
		
		echo 'DIE IN A FIRE!';
	//	die;
	}

	function loader()
	{
		
		//echo task(0);
		
			//if(parent::task(0) == 'blog') echo 'woot';
			

	}



	function listall() {
		
		return array(0,1,2,3,4,5);
		
	}

	
	
}
