{eF_template_appendTitle title = $T_PLUGIN_TITLE link = $T_BASE_URL}
{capture name = 't_annotations'}
<!--ajax:myAnnotations-->
    <div class="table-responsive">
        <table style="width:100%" class="sortedTable table" data-sort="4" data-order="desc" size="{$T_TABLE_SIZE}" data-ajax="1" id="myAnnotations"
               data-rows="{$smarty.const.G_DEFAULT_TABLE_SIZE}" url="{$T_BASE_URL}">
            <tbody>
            <tr class="topTitle">
                <td class="topTitle" name="formatted_name">{"User"|ef_translate}</td>
				<td class="topTitle centerAlign noSort" name="color">{"Color"|ef_translate}</td>
                <td class="topTitle" name="text">{"Text"|ef_translate}</td>
                <td class="topTitle centerAlign noSort" name="tags">{"Tags"|ef_translate}</td>
                <td class="topTitle centerAlign" name="created_at" data-order="desc">{"Date"|ef_translate}</td>
                <td class="topTitle centerAlign noSort" name="operations" data-order="desc">{"Operations"|ef_translate}</td>
            </tr>
            {foreach name = 'annotations' key = 'key' item = 'annotation' from = $T_DATA_SOURCE}
                <tr class="defaultRowHeight {cycle values = "oddRowColor, evenRowColor"}">
                    <td>
                        {$annotation.user}
                    </td>
					<td class="centerAlign">{$annotation.color}</td>
                    <td>{$annotation.text}</td>
                    <td class="centerAlign" style="min-width:150px;">
                        {foreach from=$annotation.tags item=tag}
                            {$tag}
                        {/foreach}
                    </td>
                    <td class="centerAlign" style="min-width:150px;">{$annotation.date}</td>
                    <td class="centerAlign" style="min-width:170px;">
                        <a href="javascript:void(0)" data-page="{$annotation.page}" data-id="{$annotation.annotation_id}" class="fa fa-lg fa-arrow-circle-right fa-fw visit-annotation" title="Visit annotation page"></a>
                        {if $T_CURRENT_USER.id eq $annotation.user_id}
                            <!--<a href="javascript:void(0)" data-annotationid="{$annotation.annotation_id}" class="sharedAnnotationUsers fa fa-lg fa-share-alt fa-fw" title="See what users this annotation is shared with"></a>-->
                            <a href="{eF_template_url extend=$T_BASE_URL url = ['edit_annotation'=>$annotation.annotation_id]}" class="fa fa-lg fa-fw fa-pencil" title="Edit annotation"></a>
                            {if $annotation.isShared}
                                <a href="javascript:void(0)" data-annotationid="{$annotation.annotation_id}" class="fa fa-lg ajaxHandle fa-fw fa-user-times unshare-annotation" title="Unshare annotation"></a>
                            {/if}
                        {/if}
                        <a href="javascript:void(0)" title="Delete" data-label="Delete" data-label2="Delete and don't ask again" class="ef-grid-delete ajaxHandle fa fa-lg fa-trash fa-md fa-fw" data-url="/annotationapi/delete/{$annotation.annotation_id}" data-message="Are you sure you want to delete this annotation? You will not be able to restore it later."></a>
                    </td>
                </tr>
                {foreachelse}
                <tr class="defaultRowHeight oddRowColor">
                    <td class="emptyCategory" colspan="100%">-</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
<!--/ajax:myAnnotations-->


<script>
    
    //tags filter
    var selectedTags=[];
    var gTagFilters='';
    $(document).on('click', '.ef-filter-annotations-by-tags', function () {
        if ($(this).hasClass('selected-tag')) {
            var removeItem=$(this).data('id');
            selectedTags=$.grep(selectedTags, function(value) {
                return value != removeItem;
            });
        }
        else{
            selectedTags.push($(this).data('id'));
        }
        var tagFilters=selectedTags.join(',');
        if(gTagFilters.length&&tagFilters.length){ //at least one tag filter selection has already been done and we select a new one
            $.fn.st.setAjaxUrl('myAnnotations', $.fn.st.getAjaxUrl('myAnnotations').replace(gTagFilters, '/selected_tags/'+tagFilters));
            gTagFilters='/selected_tags/'+tagFilters;
        }
        else if (!gTagFilters.length&&tagFilters.length) { //first tag filter selection
            //console.log('afasef');
            $.fn.st.setAjaxUrl('myAnnotations', $.fn.st.getAjaxUrl('myAnnotations') + '/selected_tags/' + tagFilters);
            gTagFilters='/selected_tags/'+tagFilters;
        }
        else if(gTagFilters.length&&!tagFilters.length){ //removed all tag filters that where selected
            $.fn.st.setAjaxUrl('myAnnotations', $.fn.st.getAjaxUrl('myAnnotations').replace(gTagFilters, ''));
            gTagFilters='';
        }
        $.fn.st.redrawPage('myAnnotations', true);
    });
    
    //user filter
    var gUserFilter='';
    $(document).on('click', '.ef-filter-annotations-by-user', function () {
        if ($(this).hasClass('selected-user')) {
            $.fn.st.setAjaxUrl('myAnnotations', $.fn.st.getAjaxUrl('myAnnotations').replace(gUserFilter, ''));
            gUserFilter='';
        } else {
            $.fn.st.setAjaxUrl('myAnnotations', $.fn.st.getAjaxUrl('myAnnotations') + '/selected_user/' + $(this).data('userid'));
            gUserFilter='/selected_user/' + $(this).data('userid');
        }
        $.fn.st.redrawPage('myAnnotations', true);
    });
    
    $(document).on('click', '.unshare-annotation',function(){
        var annotation_id=$(this).data('annotationid');
        $.ajax(
                {
                    url: 'annotationapi/unshare/'+annotation_id,
                    success: function(data){
                            if(data.success){
                                eFront.Notification.Show({
                                    icon: "smile-o",
                                    head: 'Success!',
                                    body: "Operation completed successfully",
                                    type: "success"
                                });
                                $.fn.st.redrawPage('myAnnotations', true);
                            }
                            else{
                                eFront.Notification.Show({
                                    icon: "remove", type: "error",
                                    head: '{"Something went wrong."|ef_translate}',
                                    body: "Operation failed"
                                });
                            }
                    }
                }
        );
    });
    
    $(document).on('click', '.visit-annotation', function(){
        console.log($(this).data('page'));
        sessionStorage.setItem('annotation_id',$(this).data('id'));
        window.location=$(this).data('page');
        //$.fn.st.setAjaxUrl('sharedAnnotation', $.fn.st.getAjaxUrl('sharedAnnotation') + '/annotation/' + $(this).data('annotationid'));
        //$.fn.st.redrawPage('sharedAnnotation', true);
        //$.fn.efront('modal', { 'header':'My modal title', 'body':'{$smarty.capture.t_annotation_users}' });
    });
    
</script>
{/capture}

{capture name = 't_annotation_users'}
<!--ajax:sharedAnnotation-->
    <div class="table-responsive">
        <table style="width:100%" class="sortedTable table" data-sort="0" size="{$T_TABLE_SIZE}" data-ajax="1" id="sharedAnnotation"
               data-rows="{$smarty.const.G_DEFAULT_TABLE_SIZE}" url="{$T_BASE_URL}">
            <tbody>
            <tr class="topTitle">
                <td class="topTitle" name="user">{"User"|ef_translate}</td>
                <td class="topTitle centerAlign" name="operations" data-order="desc">{"Operations"|ef_translate}</td>
            </tr>
            {foreach name = 'sharedAnnotation' key = 'key' item = 'users' from = $T_DATA_SOURCE}
                <tr class="defaultRowHeight {cycle values = "oddRowColor, evenRowColor"}">
                    <td>
                        {$users.user}
                    </td>
                    <td class="centerAlign" style="min-width:100px;">
                        <a href="javascript:void(0)" title="Unshare" data-label="Unshare" data-label2="Unshare and don't ask again" class="ef-grid-delete ajaxHandle fa fa-lg fa-minus-circle fa-md fa-fw" data-url="/annotationapi/unshare/1/annotation/{$users.annotation_id}/user/{$users.user_id}" data-message="Unshare the annotation for this user?"></a>
                    </td>
                </tr>
                {foreachelse}
                <tr class="defaultRowHeight oddRowColor">
                    <td class="emptyCategory" colspan="100%">-</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
<!--/ajax:sharedAnnotation-->


{/capture}

{capture name = 't_annotation_edit'}

    {eF_template_printForm form = $T_EDIT_ANNOTATION}
    <style>
        #editAnnotation #mce_3{ z-index:inherit !important; }
    </style>
    <script>
        //var invalidCharList = ['"="', "'='", '="', '"=', "'=", "='", '"', "'"]; This would be needed for tag filtering
        var annotation = {$T_ANNOTATION|@json_encode nofilter};
        $('#annotation-tags').selectize({
            placeholder: 'Add tags',
            create: true,
            duplicates: false,
            plugins: ['remove_button'],
            onInitialize: function () {
                window.selectize_instance = this;
            },
            onItemAdd: function (value) {
                this.blur();
            },
            onItemRemove: function (value) {
                this.blur();
            },
            render: {
                option_create: function (data, escape) {
                    return '<div class="create">' + $.fn.efront('translate', 'Add') + ' <strong>' + escape(data.input) + '</strong>&hellip;</div>';
                }
            }
        });
        function fillInFileds(annotation){
            $('#ann-comment').val(annotation.text || "");
            if (annotation.tags) {
                var sel = $('#annotation-tags').data('selectize');
                sel.clear();
                for (var i = 0; i < annotation.tags.length; i++) {
                    if(annotation.tags[i]!=''){
                        sel.addOption({
                            text: annotation.tags[i],
                            value: annotation.tags[i]
                        });
                        sel.addItem(annotation.tags[i]);
                    }
                }
            }
            if(annotation.color){
		$('#annotation-color').spectrum("set", annotation.color);
            }
            else{
                $('#annotation-color').spectrum("set", 'rgb(255, 255, 10)');
            }
        }
        $('#annotation-color').spectrum({
                preferredFormat: "rgb",
                color: "rgb(255, 255, 10)"
        });
        
        fillInFileds(annotation);
        var tinymceOptions={
                selector: "#ann-comment",
                plugins: "link table image autolink charmap code textcolor colorpicker emoticons fullscreen",
                menubar: false,
                toolbar_items_size: 'small',
                toolbar: "bold italic | forecolor backcolor | table | link | image | charmap emoticons | code | fullscreen",
                statusbar: false,
                image_dimensions: false,
				setup: function(ed) {
					ed.on('change', function(e) {
                        //set the modification in the textarea of annotator
                        $("#ann-comment").val(tinymce.activeEditor.getContent());
					});
				}
        };				
        
        
        tinymce.init(tinymceOptions);
        
        
        $('#ann-update').on('click',function(){
            var data = $.extend(annotation,{ 'tags':$('#annotation-tags').val().split(','),'text':$('#ann-comment').val(), 'sharedData': $('#ann-sharedData').val(), 'color': $('#annotation-color').val() });
            $.ajax({
                'url': '/annotationapi/update/'+annotation.id,
                'dataType': 'json',
                'method': 'PUT',
                'data': JSON.stringify(data),
                'success': function(){
                    annotation=data;
                    fillInFileds(annotation);
                    
                    //$( document ).ready(function(){
                        eFront.Notification.Show({
                            icon: "smile-o",
                            head: 'Success!',
                            body: "Operation completed successfully",
                            type: "success"
                        });
                    //});
                }
            });
        });
    </script>
{/capture}

{if $smarty.get.view_users}
    {eF_template_printBlock data = $smarty.capture.t_annotation_users}
{else if $smarty.get.edit_annotation}
    {eF_template_appendTitle title = 'Edit annotation' link={eF_template_url extend=$T_BASE_URL url = ['edit_annotation'=>$T_ANNOTATION.id]}}
    {eF_template_printBlock data = $smarty.capture.t_annotation_edit}
{else}
    {eF_template_printBlock data = $smarty.capture.t_annotations}
{/if}
