<?php
class Backend_Page_Controller extends Backend_Controller_Crud_Vc
{

    public function indexAction()
    {
        parent::indexAction();
        
        $resource = Resource::getInstance();     
        $resource->addJs('/js/app/system/BlocksPanel.js' , 3); 
        
        $fModules = Config::factory(Config::File_Array, $this->_configMain->get('frontend_modules'));        
        $funcList = array(
        	array('id'=>'','title'=>'---')
        );    
           
        foreach ($fModules as $code=>$config)
        	$funcList[] = array('id'=>$code  , 'title'=>$config['title']);
        
        $resource->addInlineJs('
        	var aFuncCodes = '.json_encode($funcList).';
        ');
    }
    
    /**
     * Get list of pages as a data tree config
     */
    public function treelistAction()
    {
         $pagesModel = Model::factory('Page');
         Response::jsonArray($pagesModel->getTreeList( array( 'id','parent_id','published','code')));
    }
    
    /**
     * Get pages list as array
     */
    public function listAction()
    {
          $pageModel = Model::factory('Page');
          $data = $pageModel->getListVc(
          	  array('sort'=>'order_no','dir'=>'ASC'), 
          	  false, 
          	  false,
              array(
                  'id',
                  'parent_id',
                  'menu_title',
                  'published',
                  'code' ,
                  'date_updated',
                  'date_created',
                  'published_version'
              )
              ,'user', 'updater'
         );  
          
         if(empty($data))
            Response::jsonSuccess(array()); 
          
         $ids = Utils::fetchCol('id', $data);
         $maxRevisions = Model::factory('Vc')->getLastVersion('page',$ids);
            
         foreach ($data as $k=>&$v)
         {
             if(isset($maxRevisions[$v['id']]))
                  $v['last_version'] = $maxRevisions[$v['id']];
             else
                  $v['last_version'] = 0;   
         }
         unset($v);    
         Response::jsonSuccess($data);
    }
    
    /**
     * Get blocks
     */
    public function blocklistAction()
    {
         $blocksModel = Model::factory('Blocks');
         $data = $blocksModel->getListVc(false,false,false,array('id','title','is_system','published'));

         foreach ($data as $k=>&$v)
         	$v['deleted'] = false;
      
         Response::jsonSuccess($data);
    }
    
    /**
     * Check if page code is unique
     */
    public function checkcodeAction()
    {
        $id = Request::post('id', 'int', 0);
        $code = Request::post('code','string',false); 
         
        $code = Filter::filterValue('pagecode', $code);
        
        $model = Model::factory('Page');
        
        if($model->checkUnique($id , 'code' , $code))
            Response::jsonSuccess(array('code'=>$code));
        else 
            Response::jsonError($this->_lang->SB_UNIQUE);    
    }
    
    /**
     * Change page sorting order
     */
    public function sortpagesAction()
    { 
       $this->_checkCanEdit();
    	
       $id = Request::post('id','integer',false);
       $newParent = Request::post('newparent','integer',false);
       $order = Request::post('order', 'array' , array());
     
       if(!$id || !strlen($newParent) || empty($order))
            Response::jsonError($this->_lang->WRONG_REQUEST);     
                       
       try{                
           $pObject = new Db_Object('page' , $id);
           $pObject->set('parent_id', $newParent);  
           $pObject->save();
           Model::factory('Page')->updateSortOrder($order);
           Response::jsonSuccess();
        } catch (Exception $e){
           Response::jsonError($this->_lang->CANT_EXEC);
        }
    }

    public function getPostedData($objectName)
    {
    	$obj = parent::getPostedData($objectName);    	
    	$posted = Request::postArray();
    	
    	if(isset($posted['blocks']))
    	{	 
    		if(strlen($posted['blocks']))
    			$posted['blocks'] = serialize(json_decode($posted['blocks'] , true));
    		else
    			$posted['blocks'] = serialize(array());
    	}
    	else
    	{
    		$posted['blocks'] = serialize(array());
    	}
    	
    	try{
    		$obj->set('blocks',  $posted['blocks']);
    	}catch (Exception $e){
    		$errors['blocks'] = $this->_lang->INVALID_VALUE;
    		Response::jsonError($this->_lang->FILL_FORM , $errors);
    	}
    	return $obj;	       
    }
    
    /**
     * Find staging URL
     * @param Db_Object $obj
     * @return string
     */
    public function getStagingUrl(Db_Object $obj)
    {
    	return Request::url(array($obj->code));
    }
    
    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud_Vc::_loadData()
     */
    protected function _loadData(Db_Object $object , $version)
    {
    	$data = parent::_loadData($object, $version);    	
    	
    	$theme = 'default';
    	
    	if(file_exists($this->_configMain->get('themes') . $object->get('theme') . '/layout_cfg.php'))
    		$theme = $object->get('theme');
    	
    	try{
    		$config = Config::factory(Config::File_Array , $this->_configMain->get('themes') . $theme . '/layout_cfg.php');
    	}catch (Exception $e){
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	}
    	
    	$conf = $config->__toArray();
    	$items = array();
    	 
    	if(isset($conf['items']) && !empty($config['items']))
    	{
    		foreach ($conf['items'] as $k=>$v)
    		{
    			$v['code'] = $k;
    			$items[] =$v;
    		}
    		$conf['items'] = $items;
    	}
    	
    	/*
    	 * Collect blocks info
    	 */
    	if(!empty($data['blocks']))
    	{
    		$blocksCfg = unserialize($data['blocks']);
    		if(!empty($blocksCfg))		
    			foreach ($blocksCfg as $key=>$value)			
    				$blocksCfg[$key] = array_values($this->_collectBlockLinksData($value));
    				
    		$data['blocks'] =array('config'=>$conf,'blocks'=>$blocksCfg);
    	}
    	else
    	{
    		$data['blocks'] = array('config'=>$conf,'blocks'=>array());
    	}
    	
    	return $data;
    }
    
    public function blockconfigAction()
    { 
        $theme = Request::post('theme', 'string', 'default');
 
    	try{
    	    $config = Config::factory(Config::File_Array , $this->_configMain->get('themes') . $theme . '/layout_cfg.php');
    	}catch (Exception $e){
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	}    
    		
    	$conf = $config->__toArray();
    	$items = array();
    	
    	if(isset($conf['items']) && !empty($config['items']))
    	{
    	    foreach ($conf['items'] as $k=>$v){
    	        $v['code'] = $k;
    	        $items[] =$v;
    	    }
    	    $conf['items'] = $items;
    	}
    	
    	Response::jsonSuccess($conf);
    }
    
	/**
     * Prepear data for linked field component
     * @param array $data
     * @return array
     */
    protected function _collectBlockLinksData(array $data)
    {
             
        $ids = Utils::fetchCol('id', $data);
        $data =  Utils::rekey('id', $data);

        $obj = new Db_Object('Blocks');
        $model = Model::factory('Blocks');
        
        $fields = array('id' , 'title'=>$obj->getConfig()->getLinkTitle(),'is_system');
        $usedRC = $obj->getConfig()->isRevControl();
        if($usedRC)
            $fields[] = 'published';
        
        $odata = $model->getItems($ids , $fields);  
        if(!empty($data))
            $odata = Utils::rekey('id', $odata);
        /*
         * Find out deleted records
         */ 
        $deleted = array_diff($ids, array_keys($odata));  
        
        $deletedData = array();

        $result = array();
        foreach ($ids as $id)
        {
            if(in_array($id, $deleted)){
            	 $title = '';
            	 if(isset($data[$id]['title']))
            	 	$title = $data[$id]['title'];
                 $item = array('id'=>$id , 'deleted'=>1 , 'title'=>$title, 'published'=>1,'is_system'=>0);
                 if($usedRC)
                     $item['published'] = 0;
            } else{
                $item = array('id'=>$id , 'deleted'=>0 , 'title'=>$odata[$id]['title'],'published'=>1,'is_system'=>$odata[$id]['is_system']);
                if($usedRC){
                     $item['published'] = $odata[$id]['published'];     
                 }
            }
            $result[] = $item;
        }  
        return $result;  
    }
    
    public function publishAction()
    {
        $id = Request::post('id','integer', false);
        $vers = Request::post('vers' , 'integer' , false);
        
        if(!$id || !$vers)
            Response::jsonError($this->_lang->WRONG_REQUEST);
        
        if(!User::getInstance()->canPublish($this->_module))
             Response::jsonError($this->_lang->CANT_PUBLISH);

        try{
            $object = new Db_Object($this->_objectName , $id);  
        }catch(Exception $e){
            Response::jsonError($this->_lang->CANT_EXEC);
        }      

        $acl = $object->getAcl();
        if($acl && !$acl->canPublish($object))
            Response::jsonError($this->_lang->CANT_PUBLISH);
        
        $vc= Model::factory('Vc');
        
        $data = $vc->getData($this->_objectName , $id , $vers);       
        
        /*
         * Do not publish some data
         * parent_id and order_no can be changed outside of version control
         */
        $publishException = array('id' , 'order_no' , 'parent_id');
        foreach ($publishException as $field)
        	if(array_key_exists($field, $data))
        		unset($data[$field]);
 		
        if(empty($data))
            Response::jsonError($this->_lang->CANT_EXEC);

        $objectConfig = $object->getConfig();
        
         foreach ($data as $k=>$v)
         {
             if($object->fieldExists($k))
             {
                 if($objectConfig->isMultiLink($k) && !empty($v))
                     $v = array_keys($v);         
                 try{
                     $object->set($k , $v);
                 }catch (Exception $e){
                     Response::jsonError($this->_lang->VERSION_INCOPATIBLE);
                 }
             } 
         }   
             
         if(isset($data['blocks']) && strlen($data['blocks'])){
             $blocks = unserialize($data['blocks']);
             if(empty($blocks))
                 $blocks = array();
                 
             if(!Model::factory('Blocks')->setMapping($id , $blocks))
                 Response::jsonError($this->_lang->CANT_EXEC.' code 2');
         }

        $object->set('published_version' , $vers);
        $object->set('published', true); 
             
        if($vers == 1 || empty($object->date_published))
        	$object->set('date_published' , date('Y-m-d H:i:s'));
        
        if(!$object->save(false))
            Response::jsonError($this->_lang->CANT_EXEC);
   
        
        if($objectConfig->hasHistory())
        {
	         $hl = Model::factory('Historylog');
	         $hl->log(
	                          User::getInstance()->id,
	                          $object->getId(),
	                          Model_Historylog::Publish ,
	                          $object->getTable()
	                 );   
        }
        Response::jsonSuccess();
    }
    
    /**
     * (non-PHPdoc)
     * @see Backend_Controller_Crud::deleteAction()
     */
    public function deleteAction()
    {
        $this->_checkCanDelete();
            
        $id = Request::post('id','integer', false);
        
		if(!$id)
        	Response::jsonError($this->_lang->WRONG_REQUEST);
            
        try{
        	$object = new Db_Object($this->_objectName , $id);
        }catch(Exception $e){
        	Response::jsonError($this->_lang->WRONG_REQUEST);
        }
        
        $acl = $object->getAcl();
        if($acl && !$acl->canDelete($object))
            Response::jsonError($this->_lang->CANT_DELETE);
        
        $childIds = Model::factory('Page')->getList(false,array('parent_id'=>$id),array('id'));
		if(!empty($childIds))
			Response::jsonError($this->_lang->REMOVE_CHILDREN);
          
       	if($this->_configMain->get('vc_clear_on_delete'))
    		Model::factory('Vc')->removeItemVc($this->_objectName , $id);
             
		if(!$object->delete()){
        	Response::jsonError($this->_lang->CANT_EXEC);
		}
		
       	Model::factory('Blockmapping')->clearMap($id);
        Response::jsonSuccess();
    }
    
    /**
     * Get themes list
     */
    public function themeslistAction()
    {
        $themes = File::scanFiles($this->_configMain->get('themes'),false,false, File::Dirs_Only);
        $result = array();
        if(!empty($themes))
        {
            foreach ($themes as $name){
                $code = basename($name);
                if($code[0]!='.')
                	$result[] = array('id'=>$code,'title'=>$code);
            }    
        }
        
        if($this->_configMain->get('allow_externals'))
        {
        	$config = Config::factory(Config::File_Array, $this->_configMain->get('configs') . 'externals.php');
        	$eExpert = new Externals_Expert($this->_configMain, $config);
        	$themes = $eExpert->getThemes();
        		
        	if(!empty($themes))
        		foreach ($themes as $k=>$path)
        			$result[] = array('id'=>$k,'title'=>$k);
        } 
        Response::jsonSuccess($result);
    }
    
    /**
     * Get list of default blocks
     */
    public function defaultblocksAction()
    {	
    	$blocks = Model::factory('Blockmapping');
    	$list = $blocks->getList(false , array('page_id'=>null), array('id'=>'block_id','place'));
    	
    	try{
    	    $config = Config::factory(Config::File_Array , $this->_configMain->get('themes') . 'default' . '/layout_cfg.php');
    	}catch (Exception $e){
    		Response::jsonError($this->_lang->WRONG_REQUEST);
    	}     	
    	
    	if(!empty($list))
    	{
    		$list = Utils::groupByKey('place', $list);
  		
    	    foreach ($list as $key=>&$value)
    	    {
               $value = array_values($this->_collectBlockLinksData($value));
            }
            unset($value);   
    	}
    	else
    	{
    		$list = array();
    	}
 	
    	Response::jsonSuccess(array('config'=>$config->__toArray(),'blocks'=>$list));
    }
    
    /**
     * Save default blockmap
     */
    public function defaultblockssaveAction()
    {	  	
    	$this->_checkCanEdit();
    	
    	$data = Request::post('blocks', 'raw', '');
    	if(strlen($data))
    		$data = json_decode($data , true);
    	else
    		$data = array();

    	
    	$blockMapping = Model::factory('Blockmapping');
    	$blockMapping->clearMap(0);
    	
    	if(!empty($data))
    		foreach ($data as $place=>$items)
    			$blockMapping->addBlocks(0 , $place , Utils::fetchCol('id', $items));
    	    	
    	$blockManager = new Blockmanager();
    	$blockManager->invalidateDefaultMap();
    	Response::jsonSuccess();
    } 
}