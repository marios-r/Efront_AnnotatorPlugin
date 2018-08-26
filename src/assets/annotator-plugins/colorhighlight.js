Annotator.Plugin.ColorHighlight = function (element, options) {

    var plugin = {};

    plugin.pluginInit = function () {
        this.annotator.subscribe("annotationCreated", this.colorHighlight);
		this.annotator.subscribe("annotationUpdated", this.colorHighlight);
		this.annotator.subscribe("annotationsLoaded", this.colorHighlights.bind(this));
        this.field = this.annotator.editor.addField({
            label: "Pick a color…",
            load: this.updateColorField.bind(this),
            submit: this.setColor
        });
		$(this.field).find('input').spectrum({
			preferredFormat: "rgb",
			color: "rgb(255, 255, 10)"
		});
    };

    plugin.updateColorField = function(field, annotation) {	
		if(annotation.color){
			$(field).find('input').spectrum("set", annotation.color);
		}
		else{
			$(field).find('input').spectrum("set", 'rgb(255, 255, 10)');
		}
    };
	
	plugin.colorHighlights = function(annotations){
		for(var i=0; i<annotations.length; i++){
			this.colorHighlight(annotations[i]);
		}
	};

    plugin.setColor = function (field, annotation) {
		annotation.color = $(field).find('input').val();
        return annotation.color = $(field).find('input').val();
    };

    plugin.colorHighlight = function (annotation) {
		if(annotation.color){
			var rgb = annotation.color;
			rgb = rgb.substring(4, rgb.length-1)
					.replace(/ /g, '')
					.split(',');
			var rgba='rgba('+rgb[0]+', '+rgb[1]+', '+rgb[2]+',0.3)';
			for(var i=0;i<annotation.highlights.length;i++){
				$(annotation.highlights[i]).css('background',rgba);
			}
		}
		else{
			for(var i=0;i<annotation.highlights.length;i++){
				$(annotation.highlights[i]).css('background','rgba(255, 255, 10, 0.3)');
			}		
		}
        return annotation;
    };

    return plugin;
}