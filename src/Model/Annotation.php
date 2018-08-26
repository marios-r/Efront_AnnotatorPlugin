<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Efront\Plugin\Annotator\Model;
use Efront\Model\BaseModel;
use Efront\Model\Database;
use Efront\Model\User;
use Efront\Plugin\Annotator\Model\SharedAnnotation;
use Efront\Plugin\Annotator\Model\AnnotationTag;


/**
 * Description of Annotation
 *
 * @author User
 */
class Annotation extends BaseModel{
    
    const DATABASE_TABLE = 'plugin_annotations';
    //put your code here
    protected $_fields = array(
        'id'            => 'id',
        'user_ID'       => 'id',
        'page'          =>'',
        'text'          => 'wysiwig',
        'quote'         =>'',
        'created_at'    =>'timestamp',
        'updated_at'    =>'timestamp',
        'startOffset'   =>'int',
        'endOffset'     =>'int',
        'start'         =>'',
        'end'           =>'',
		'color'			=>''
    );
 
    public $id;
    public $user_ID;
    public $text;
	public $page;
    public $quote;
    public $start;
    public $end;
    public $startOffset;
    public $endOffset;
    public $created_at;
    public $updated_at;  
	public $color;
    
}
