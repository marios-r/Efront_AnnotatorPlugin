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
 * This is the model of annotations that are shared with other users.
 *
 * @author Marios Raptis
 */



class AnnotationSharedToUser extends BaseModel
{
    const DATABASE_TABLE = 'plugin_annotation_shared_to_user';
    protected $_fields = array(
                    'id' => 'id',
                    'user_ID' => 'id',
                    'annotation_ID' => 'id',
    );
    
    public $id;
    public $users_ID;
    public $annotation_ID;
	
	public function delete($annotation_id, $user_id){
            $this->checkId($annotation_id);
            $this->checkId($user_id);
            $query = Database::getInstance()->execute (
                    'DELETE FROM '.self::DATABASE_TABLE.' WHERE user_ID='.$user_id.' AND annotation_ID='.$annotation_id
                );
            return $query;
	}
        
        public function unshare($annotation_id){
            $this->checkId($annotation_id);
            $query = Database::getInstance()->execute (
                    'DELETE FROM '.self::DATABASE_TABLE.' WHERE annotation_ID='.$annotation_id
                );
            return $query;
        }
    
}
