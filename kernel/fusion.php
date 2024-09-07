<?

class fusion 
{
	function init() 
	{
		$this->config = $this->getConfig(getcwd().'/kernel/config.xml');
		$this->initMysql();
		$this->getConfigDB();
	
		// initiate skin vars
		$this->config->themeUrl = 'http://'.$this->config->mainurl.'themes/'.$this->config->theme.'/';
		$this->config->themeDir = $this->config->maindir.'themes/'.$this->config->theme.'/';
		
		$this->hookHead = array();
		$this->hookContent = array();
		$this->hookFoot = array();
		
		$this->routers = array(); // figures out what to do via url parameters
		$this->pluginsFetch(); 
			
		$this->pageurl 	= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		$this->task = $this->urlparse($this->pageurl); // tasks = url parts
		
		// determine what to do
		
		$this->routing();
		
		
	}
	
	function routingParsesingle($i, $routers)
	{
		
		if($this->task($i+1) != null)
		{
			// another task above this one
			@$this->routingParsesingle($i+1, $routers[$this->task($i+1)]);
			
		} else {
			
			// last task in chain
			$routers = $routers['index'];
	
			$class = $routers['@attributes']['plugin'];

			if(isset($routers['@attributes']['run'])) 
			{
					
				$method = $routers['@attributes']['run'];
				eval('$this->'.$class.'->'.$method.'();');	
	
			} 
			
			if(isset($routers['@attributes']['include'])) 
			{
				$include = $routers['@attributes']['include'];	
				$this->hookContent[] = $include;	
	
			} 
			
			// var_dump($routers[$this->task($i)]['index']['@attributes']['run']); 
			// echo '<br>';
			
		}
		
	}
	
	function routing() 
	{
	// All avaiable plugins have already supplied routers, this figures out
	// how to make a page... mommy, this is where page rendering comes from
	
		$routers = json_decode(json_encode($this->routers), true);
		
		// var_dump($routers);
	
		$i = 1;
		if(!is_null(@$routers[$this->task($i)])) $this->routingParsesingle($i, $routers[$this->task(1)]);
		
		if(is_null($this->task(1))) $this->hookContent[] = $this->config['maindir'].'themes/main.phtml';
		if(empty($this->hookContent)) $this->hookContent[] = $this->config['maindir'].'themes/error404.phtml';
		
	
	}
	
	function initMysql()
	{
		require_once('mysql.php');
		$this->mysql = new mysql();
		
		$c = $this->config->database;
		$this->mysql->dbConnect($c);	
	}
		
	function getConfig($xml)
	{
		return simplexml_load_file($xml);	
	}
	
	
	public function task($id = null) 
	{
		if($id == null) {
			return $this->task;
		} else {
				
			if(isset($this->task[$id-1]))
			{	
				return (string) $this->task[$id-1];
			} else {
				return null;
			}
				
		}
	}
	
	function devmode($toggle = true) 
	{
		if($toggle = true)
		{
			ini_set('error_reporting', E_ALL);
			ini_set('display_errors', 1);
			return true;
		} else {
			ini_set('display_errors', 0);
			return false;
		}
	}
	
	function pageTitle() {
		return 'Fusion Baby!';
	}
	
	function urlparse($url = null) 
	{
		$thisurl = str_replace($this->config->mainurl, '', $url);
		$thisurl = explode('/', $thisurl);
		$thisurl = array_values(array_filter($thisurl));

		return $thisurl;	
	}
	
	function pluginsLoad($node, $path) 
	{
		// echo $this->maindir.$path; return;
		if(is_file($this->config['maindir'].$path.'/plugin.php'))
			require_once($this->config->maindir.$path.'/plugin.php');

		if(class_exists($node)) 
		{
			$this->$node = new $node();
			$this->$node->init();
			
		}
		
		if(is_file($this->config['maindir'].$path.'/router.xml')) 
		{
	
			$this->routerLoad($this->config['maindir'].$path.'/router.xml');
		}
		
		
			
	}
	
	function routerLoad($router) 
	{
		$it = (array) simplexml_load_file($router);
		$this->routers = array_merge($this->routers, $it);		
	}

	function pluginsFetch()
	{
		if ($handle = opendir('./plugins/')) {
    	while (false !== ($entry = readdir($handle))) 
    	{
	        if ($entry != "." && $entry != "..") 
	        {
				if((is_dir('./plugins/'.$entry)) && (is_file('./plugins/'.$entry.'/plugin.php'))) {
					$this->pluginsLoad($entry, 'plugins/'.$entry);
				}  else {
	            	$this->pluginsLoad($entry, 'plugins/'.$entry);
				}	
			}
   		 }
    	closedir($handle);
		}
	}
	
	function render($path = null) {
		
		if($path == null) $path = $this->config->themeDir.'template.phtml';
		include($path);
	}
	
	function output($hook) 
	{
		foreach($hook as $item) {
			if(is_file($item)) 
			{
				include($item); 
				return;
			} else {
				
				if(is_file($this->config->maindir.'themes/'.$item)) 
				{
					include $this->config->maindir.'themes/'.$item;
					return;
				} else {
				
					if(is_file($this->config->maindir.'plugins/'.$item)) 
					{
						include $this->config->maindir.'plugins/'.$item;
						return;

					}
					
				}
				
			}
		}		
	}
	
	function getConfigDB() 
	{
		$sql = $this->mysql->query("select * from ~~config");
		while($row = mysql_fetch_array($sql)) 
		{
			$this->config->$row['name'] = $row['value'];
		}
		return;
	}

}