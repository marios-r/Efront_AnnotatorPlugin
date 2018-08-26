{include file='file:[base]layout/navbar.tpl'}
<script>
    var annt='<li><a class="dropdown-item" href="{eF_template_url url=["ctg"=>"Annotator"]}">{"Annotations"|ef_translate}</a></li>';
    $('#ef-navigation .hidden-xs:not(".is-read") ul').append(annt);
</script>    
