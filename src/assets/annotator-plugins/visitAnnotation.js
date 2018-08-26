Annotator.Plugin.VisitAnnotation = function (element, options) {

    var plugin = {};

    plugin.pluginInit = function () {
        this.annotator.subscribe("annotationsLoaded", this.onAnnotationsLoaded.bind(this));
    }; 
    
    plugin.onAnnotationsLoaded = function(){
		if(sessionStorage.getItem('annotation_id')){
			var elements = this.isOnPage(sessionStorage.getItem('annotation_id'));
			if (!elements.length) {
				console.log('This annotation has not loaded');
			} else {
				var viewPanelHeight = $(window).height();
				$('html, body').animate({
					scrollTop: elements.offset().top - (viewPanelHeight / 2)
				}, 2000);
				this.annotator.showViewer([elements.data('annotation')],elements.position())
			}
			sessionStorage.removeItem('annotation_id');
		}
    }
    
    plugin.isOnPage = function(id){
        return $('.annotator-hl[data-annotation-id='+id+']');
    }
    
    return plugin;
}