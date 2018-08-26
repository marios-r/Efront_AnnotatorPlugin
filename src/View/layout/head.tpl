{include file = "file:[base]layout/head.tpl"}

<link rel="stylesheet" href="plugins/Annotator/assets/annotator-full.1.2.10/annotator.min.css">
<script src="plugins/Annotator/assets/annotator-full.1.2.10/annotator-full.min.js" type="text/javascript"></script>
<script src="plugins/Annotator/assets/annotator-plugins/varutil.js" type="text/javascript"></script>
<script src="plugins/Annotator/assets/annotator-plugins/ef-share.js" type="text/javascript"></script>
<script src="plugins/Annotator/assets/annotator-plugins/selectizetags.js" type="text/javascript"></script>
<script src="plugins/Annotator/assets/annotator-plugins/visitAnnotation.js" type="text/javascript"></script>

<script src="plugins/Annotator/assets/annotator-plugins/colorhighlight.js" type="text/javascript"></script>
<script src="plugins/Annotator/assets/tinymce4.7.13/js/tinymce/tinymce.min.js"></script>
<script src="plugins/Annotator/assets/annotator-plugins/richText-annotator.js"></script>
<link href="plugins/Annotator/assets/annotator-plugins/richText-annotator.css" rel="stylesheet">

<!-- spectrum -->
<link href="plugins/Annotator/assets/spectrum/spectrum.css" rel="stylesheet">
<script src="plugins/Annotator/assets/spectrum/spectrum.js"></script>

<!-- annotator css improvements -->
<style>
.annotator-widget .selectize-dropdown-content{
    background-color:white;
}
.annotator-widget .selectize-input a::after{
    content: none;
}
.ef-content-area{
    overflow:visible;
}
.annotator-viewer .annotator-tags>.annotator-tag{
    font-size:11px;
}
.selected-tag{
    background: #4dc34d !important;
}
.annotator-controls,.annotator-controls>a{
    background-image: none !important;
}
.annotator-controls>a{
    border-radius: 0px !important;
	background-color: #2276d2 !important;
    border: 1px solid transparent !important;
    color: #ffffff !important;
    text-shadow: none !important;
}
.annotator-controls>a::after{
	-webkit-filter: invert(.8);
  	filter: invert(.8);
}
.annotator-viewer {
    width: 850px !important;
}
form.annotator-widget {
    width: 650px !important;
}
.annotator-viewer .richText-annotation img{
    height:auto !important;
}
.annotator-viewer .annotator-controls {
    line-height:13px;
}
.annotator-viewer  .annotator-isShared  {
    text-indent:0px !important;
    font: normal normal normal 14px/1 FontAwesome;
    color:black;
    margin-right:2px;
}
.sp-container button{
	background-image: none !important;
    border-radius: 0px !important;
}
</style>

<script> 
function annotate(selector){
	setTimeout( function(){ 
		$(function(){
			if($(selector).length){
				var prefixurl=location.origin+'/annotationapi';
				var annotation = $(selector).annotator();
				
				annotation.annotator('addPlugin', 'VarUtil');
				annotation.annotator('addPlugin','VisitAnnotation');
				annotation.annotator('addPlugin', 'Ef_Share');
				annotation.annotator('addPlugin', 'SelectizeTags');
				annotation.annotator('addPlugin','ColorHighlight');
				var optionsRichText = {
					tinymce:{
						selector: "li.annotator-item textarea",
						plugins: "link table image autolink charmap code textcolor colorpicker emoticons fullscreen",
						menubar: false,
						toolbar_items_size: 'small',
						toolbar: "bold italic | forecolor backcolor | table | link | image | charmap emoticons | code | fullscreen",
						statusbar: false,
						image_dimensions: false						
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
}
</script>
