<?php

namespace Efront\Plugin\Annotator\Model;
use Efront\Model\AbstractPlugin;
use Efront\Plugin\Annotator\Controller\AnnotationsController;
use Efront\Plugin\Annotator\Controller\AnnotatorController;
use Efront\Model\Database;
use Efront\Controller\BaseController;
 
class AnnotatorPlugin extends AbstractPlugin {
	const VERSION = '1.0';
 
	public function installPlugin() {
        try {
            Database::getInstance()->execute ( //ANNOTATIONS
                "CREATE TABLE IF NOT EXISTS plugin_annotations (
                    id MEDIUMINT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    user_ID MEDIUMINT(8) UNSIGNED NOT NULL,
                    text TEXT DEFAULT NULL,
                    quote TEXT DEFAULT NULL,
                    start TEXT DEFAULT NULL,
                    end TEXT DEFAULT NULL,
                    startOffset TEXT DEFAULT NULL,
                    endOffset TEXT DEFAULT NULL,
                    created_at INT(10) UNSIGNED NOT NULL,
                    updated_at INT(10) UNSIGNED NOT NULL,
                    page TEXT DEFAULT NULL,
                    color VARCHAR(40) DEFAULT NULL,
                    FOREIGN KEY(user_ID) REFERENCES users (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
            );
            Database::getInstance()->execute ( //ANNOTATIONS TO USERS
                "CREATE TABLE IF NOT EXISTS plugin_annotation_shared_to_user (
                    id MEDIUMINT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    user_ID MEDIUMINT(8) UNSIGNED NOT NULL,
                    annotation_ID MEDIUMINT(8) UNSIGNED NOT NULL,
                    FOREIGN KEY(annotation_ID) REFERENCES plugin_annotations (id) ON DELETE CASCADE,
                    FOREIGN KEY(user_ID) REFERENCES users (id) ON DELETE CASCADE,
                    CONSTRAINT UC_plugin_shared_annotations UNIQUE (user_ID,annotation_ID)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
            );
			Database::getInstance()->execute ( //TAGS
                "CREATE TABLE IF NOT EXISTS plugin_annotations_tags (
                    id MEDIUMINT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    tagname VARCHAR(200) DEFAULT NULL,
                    CONSTRAINT UC_plugin_annotations_tags UNIQUE (tagname)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
            );
            Database::getInstance()->execute ( //ANNOTATIONS TO TAGS
                "CREATE TABLE IF NOT EXISTS plugin_annotations_to_tags (
                    id MEDIUMINT(8) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                    tag_ID MEDIUMINT(8) UNSIGNED NOT NULL,
                    annotation_ID MEDIUMINT(8) UNSIGNED NOT NULL,
                    FOREIGN KEY(annotation_ID) REFERENCES plugin_annotations (id) ON DELETE CASCADE,
                    FOREIGN KEY(tag_ID) REFERENCES plugin_annotations_tags (id) ON DELETE CASCADE,
                    CONSTRAINT UC_plugin_annotations_to_tags UNIQUE (tag_ID,annotation_ID)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
            );
        } catch (\Exception $e) {
            $this->uninstallPlugin();
        }
        return $this;
    }

    public function uninstallPlugin() {
        try {
            Database::getInstance()->execute("DROP TABLE IF EXISTS plugin_annotations");
        } catch (\Exception $e) {
            // pass
        }
        return $this;
    }
 
	public function upgradePlugin() {
            if (version_compare('1.1', $this->plugin->version) == 1) {
                Database::getInstance()->execute('ALTER TABLE plugin_annotations MODIFY COLUMN created_at INT(10) UNSIGNED NOT NULL MODIFY COLUMN updated_at INT(10) UNSIGNED NOT NULL MODIFY COLUMN ranges JSON');
            }
             return $this;   
            }
        
        public function onCtg($ctg) {
            if ($ctg == $this->plugin->name) {
                BaseController::getSmartyInstance()->assign("T_CTG", 'plugin')
                    ->assign("T_PLUGIN_FILE", $this->plugin_dir.'/View/Annotator.tpl');
                $controller = new AnnotatorController();
                $controller->plugin = $this->plugin;

                return $controller;
            }
	    if ($ctg == 'annotationapi') {
                $controller = new AnnotationsController();
	        $controller->plugin = $this->plugin;
                return $controller;
            }
            else{
                return null;
            }
	}
        
        public function overridesTemplate() {
            return dirname(__DIR__).'/View';
        }
}