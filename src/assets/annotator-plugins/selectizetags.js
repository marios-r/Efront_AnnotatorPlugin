Annotator.Plugin.SelectizeTags = function (element, options) {

    var plugin = {};

    plugin.pluginInit = function () {
        var field=this.annotator.editor.addField({
            label: "Select tags or add new",
            load: this.updateTagsField.bind(this),
            submit: this.setTagsData
        });
        this.annotator.viewer.addField({
            load: this.updateViewer
        });
		$(field).find('input').selectize({
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
					return '<div class="create">Add<strong>' + escape(data.input) + '</strong>&hellip;</div>';
				}
			}
        });
    };

    plugin.updateViewer = function (field, annotation) {
        field = $(field);
        if (annotation.tags && $.isArray(annotation.tags) && annotation.tags.length) {
            return field.addClass("annotator-tags").html(function () {
                var string;
                return string = $.map(annotation.tags, function (tag) {
                    return '<span class="annotator-tag">' + Annotator.Util.escape(tag) + "</span>"
                }).join(" ")
            })
        } else {
            return field.remove()
        }
    };

    plugin.updateTagsField = function (field, annotation) {
        if ($(field).find('input').data('selectize')) {
            $(field).find('input').data('selectize').clear();
        }
        if (annotation.tags) {
            var sel = $(field).find('input').data('selectize');
            for (var i = 0; i < annotation.tags.length; i++) {
                sel.addOption({
                    text: annotation.tags[i],
                    value: annotation.tags[i]
                });
                sel.addItem(annotation.tags[i]);
            }
        }
    };

    plugin.setTagsData = function (field, annotation) {
        return annotation.tags = $(field).find('input').val().split(',');
    };

    return plugin;
}