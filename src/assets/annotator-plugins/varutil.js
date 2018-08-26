Annotator.Plugin.VarUtil = function (element, options) {

    var plugin = {};

    plugin.pluginInit = function () {
        this.annotator.subscribe("rangeNormalizeFail", this._onLoadError);
        this.annotator.subscribe("annotationCreated", this._addPage);
        this.annotator.subscribe("annotationDeleted", this.successMessage);
        this.annotator.subscribe("annotationUpdated", this.successMessage);
    }; 

    plugin._onLoadError = function (annotation, r, e) {
        console.log('\n Annotation Failure:');
        console.log(annotation);
        console.log(r);
        console.log(e);
    };
    
    plugin._addPage = function (annotation) {
        annotation.page = location.pathname;
		eFront.Notification.Show({
			icon: "smile-o",
			head: 'Success!',
			body: "Operation completed successfully",
			type: "success"
		});
        return annotation;
    };
    
    plugin.successMessage=function(a){
		if(a.id){
			eFront.Notification.Show({
				icon: "smile-o",
				head: 'Success!',
				body: "Operation completed successfully",
				type: "success"
			});
		}
    }
    
    return plugin;
}