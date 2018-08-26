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
 * Model of annotation to tag
 *
 * @author Marios Raptis
 */
class AnnotationToTag extends BaseModel
{
    const DATABASE_TABLE = 'plugin_annotations_to_tags';
    protected $_fields = array(
                    'id' => 'id',
                    'tag_ID' => 'id',
                    'annotation_ID' => 'id',
    );
    
    public $id;
    public $tag_ID;
    public $annotation_ID;
    
	public function deleteTagsOfAnnotation($annotation_id){
            $this->checkId($annotation_id);
            $query = Database::getInstance()->deleteTableData(self::DATABASE_TABLE,'annotation_ID='.$annotation_id);

            return $query;
	}
        
        public function getTagsByAnnotationTagIDs($ids){
            $query = Database::getInstance()->execute ( //DELETE ANNOTATION TAGS
                'SELECT tag FROM '.self::DATABASE_TABLE.' WHERE id IN('.$ids.')'
            );

            return $query;
        }
        
        public function insertMultipleAnnotationToTags($annotation_id,$tagIds) {
            $annotationToTagEntries=array();
            foreach($tagIds as $tagId){
                $annotationToTagEntry=array('annotation_ID'=>$annotation_id,'tag_ID'=>$tagId);
                array_push($annotationToTagEntries, $annotationToTagEntry);
            }
            $query = Database::getInstance()->insertTableDataMultiple(self::DATABASE_TABLE,$annotationToTagEntries);
            return $query;
        }
}
