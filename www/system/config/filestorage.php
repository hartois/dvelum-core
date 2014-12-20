<?php
return array(
    'adapter'=>'Orm',
	'filepath' => __DIR__ . '/../../../filestorage',
    'object'=>'filestorage',
    'check_orm_structure'=> false,
    'user_id'=>0, //default user ID
    'upload_prefix'=>'',
    'rename'=>true,
    'uploader' => 'Upload',
    'uploader_config' => array(
        'file' => array(
    		'title' => 'Files' ,
    		'extensions' => array(
    				'.doc' ,
    				'.docx' ,
    				'.pdf' ,
    				'.rar' ,
    				'.zip' ,
    				'.odt' ,
    				'.txt' ,
    				'.xls' ,
    				'.xlsx' ,
    				'.ods',
    				'.csv',
    				'.gif' ,
    				'.png' ,
    				'.jpg' ,
    				'.jpeg',
                    '.eml'
    		)
        )
    )
);