<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Efront\Plugin\Annotator\Model;
use Efront\Model\BaseModel;
use Efront\Model\Database;

/**
 * Description of annotation tag
 *
 * @author Marios Raptis
 */
class AnnotationTag extends BaseModel{
    const DATABASE_TABLE = 'plugin_annotations_tags';
    protected $_fields = array(
                    'id' => 'id',
                    'tagname' => ''
    );
    
    public $id;
    public $tagname;
    
    public function getIdByTagName($tagname){

            $query = Database::getInstance()->getTableDataSingle(self::DATABASE_TABLE, $fields="id", $where='tagname=`'.$tagname.'`', $order="", $group="", $limit = "");
            
            return $query;
        }
    
	// gets array of tags
    public function insertMultipleAnnotationTags($tags){
        $tagEntries=array();
        foreach($tags as $tag){
            $tagEntry=array('tagname'=>$tag);
            array_push($tagEntries, $tagEntry);
        }
        $query = Database::getInstance()->insertTableDataMultiple(self::DATABASE_TABLE,$tagEntries);
        return $query;
    }
    
    public function getTagsByTagName($tags) {
        $tagsInWhere=''; // `tag1`,`tag2`,`tag3`,
        foreach($tags as $tag){
            $tagsInWhere=$tagsInWhere."'".addslashes($tag)."'".",";
        }
        $tagsInWhere=substr($tagsInWhere, 0, -1);// `tag1`,`tag2`,`tag3`
        $query = Database::getInstance()->getTableData(self::DATABASE_TABLE,'*','tagname IN('.$tagsInWhere.')');
        return $query;
    }
}
