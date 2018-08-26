{include file = "file:[base]layout/closing.tpl"}

<script> 
/*function annotate(selector){
	setTimeout( function(){ 
		$(function(){
			if($(selector).length){
				var prefixurl=location.origin+'/annotationapi';
				var annotation = $(selector).annotator();
				
				annotation.annotator('addPlugin', 'VarUtil');
				annotation.annotator('addPlugin','VisitAnnotation');
				annotation.annotator('addPlugin', 'Ef_Share');
				annotation.annotator('addPlugin', 'Ef_Tags');
				annotation.annotator('addPlugin','ColorHighlight');
				var optionsRichText = {
					tinymce:{
						selector: "li.annotator-item textarea",
						plugins: "link",
						menubar: false,
						toolbar_items_size: 'small',
						toolbar: "bold italic | link",
						statusbar: false
					}
				};
				annotation.annotator('addPlugin','RichText',optionsRichText);
				annotation.annotator('addPlugin','Permissions', {
					user: { id: {$T_CURRENT_USER.id}, name:"{$T_CURRENT_USER.formatted_name}"},
					permissions: {
						'read':   [{$T_CURRENT_USER.id}],
						'update': [{$T_CURRENT_USER.id}],
						'delete': [{$T_CURRENT_USER.id}],
						'admin':  [{$T_CURRENT_USER.id}]
					  },
					userId: function (user) {
						if (user && user.id) {
						  return user.id;
						}
						return user;
					  },
					userString: function (user) {
						if (user && user.name) {
						  return user.name;
						}
						return user;
					  },
					showViewPermissionsCheckbox: false,
					showEditPermissionsCheckbox: false
				});
				annotation.annotator('addPlugin', 'Store', {
					loadFromSearch: true,
					prefix: prefixurl,
					urls: {
						create:  '/ajax/store',
						update:  '/update/:id',
						destroy: '/delete/:id',
						search:  '/ajax/search'
					}
				});
			}
		});
	}, 3000 );
}*/
</script>

