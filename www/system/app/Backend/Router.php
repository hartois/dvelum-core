<?php
class Backend_Router extends Router
{ 
	 /**
	  * Route request to the Controller
	  * @return void
	  */
	 public function route()
	 {	 	
	 	$cfg = Registry::get('backend' , 'config');
	 	
	 	$controller = $this->_request->getPart(1);
	 	$controller = Utils_String::formatClassName(Filter::filterValue('pagecode', $controller));
	 	
	 	if(in_array('Backend_' . $controller . '_Controller', $cfg->get('system_controllers')))
	 	{
	 		$controller = 'Backend_' . $controller . '_Controller';
	 	}
	 	else
	 	{
	 		$manager = new Backend_Modules_Manager();
	 		$controller = $manager->getModuleController($controller);
	 		
	 		if($controller === false)
	 		{
	 		  if(Request::isAjax())
	 		  {
	 		    Response::jsonError(Lang::lang()->get('WRONG_REQUEST').' ' . Request::getInstance()->getUri());
	 		  }
	 			$controller = 'Backend_Index_Controller';
	 		}
	 	}	 	
	 	
	 	if(class_implements($controller , 'Router_Interface')){
	 	    $controller = new $controller();
	 	    return $controller->run();
	 	}else{
	 	    $this->runController($controller,  $this->_request->getPart(2));		 	  
	 	}	 	
	 }
	 
	 public function findUrl($module)
	 {
	 	$cfg = Registry::get('main' , 'config');
	 	return Request::url(array($cfg['adminPath'] , $module),false);
	 }
}