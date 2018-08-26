Annotator.Plugin.Ef_Share = function (element, options) {

    var plugin = {};

    plugin.pluginInit = function () {
        this.annotator.subscribe('annotationViewerShown',this.updateViewers);
        this.field = this.annotator.editor.addField({
            label: "Share with…",
            load: this.updateShareField.bind(this),
            submit: this.setSharedData
        });
        $(this.field).find('input')
                .data('url', "/list")
                .data('type', 'messages')
                .data('trigger', "focus")
                .data('toggle', "popover")
                .addClass("ef-autocomplete ef-popover-toggle")
                .attr('multiple', "multiple")
                .attr('id', 'ef-annrecipient-box');
        this.efSelectize($(this.field).find('input'));
        /*this.annotator.viewer.addField({
            load : this.setUnshareField.bind(this)
        });*/
        this.annotator.viewer.element.on('click', '.annotator-isShared',this.unshare);
    };
    
    plugin.updateViewers=function(v, annotations){
        v.element.find('.annotator-annotation.annotator-item').each(function(){
            var annotation=$(this).data('annotation');
            if(annotation.isShared){
                $(this).find('.annotator-controls').prepend('<button data-id='+annotation.id+' class="fa annotator-isShared fa-lg fa-fw fa-user-times"></button>');
            }
        });
    }
    
    /*plugin.setUnshareField=function(field, annotation){
        if(annotation.isShared){
            $(field).closest('.annotator-controls').prepend('<button data-id='+annotation.id+' class="fa annotator-isShared fa-lg fa-fw fa-user-times"></button>');
        }
        $(field).remove();
    }*/

    plugin.unshare = function(e){
        console.log(this);
        console.log(e);
        var annotation_id=$(e.target).data('id');
        var _this= $(e.target);
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
                                _this.remove();
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
    }
    
    plugin.setSharedData = function (field, annotation) {
        return annotation.sharedData = $(field).find('input').val(),annotation.isShared=true;
    };

    plugin.updateShareField = function (field, annotation) {
            $(field).find('input').data('selectize').clear();
    };

    plugin.efSelectize = function (element) {
        $.fn.efront.onSelectizeReady = function () {
            $.fn.select_autocomplete.each(function (i, s) {
                if (s.name == 'recipient') {
                    s.selectize.focus();
                }
            });
        };
        
        $.fn.select_autocomplete = element.selectize({
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            optgroups: [{
                    id: 'users',
                    name: $.fn.efront('translate', 'Users'),
                    icon: 'user'
                },
                {
                    id: 'groups',
                    name: $.fn.efront('translate', 'Groups'),
                    icon: 'users'
                },
                {
                    id: 'branches',
                    name: $.fn.efront('translate', 'Branches'),
                    icon: 'code-fork'
                },
                {
                    id: 'courses',
                    name: $.fn.efront('translate', 'Courses'),
                    icon: 'book'
                },
                {
                    id: 'lessons',
                    name: $.fn.efront('translate', 'Lessons'),
                    icon: 'file-text'
                },
                {
                    id: 'files',
                    name: $.fn.efront('translate', 'Files'),
                    icon: 'files-o'
                },
                {
                    id: 'curriculums',
                    name: $.fn.efront('translate', 'Curriculums'),
                    icon: 'dictionary'
                },
                {
                    id: 'locations',
                    name: $.fn.efront('translate', 'Locations'),
                    icon: 'earth_location'
                },
                {
                    id: 'jobs',
                    name: $.fn.efront('translate', 'Jobs'),
                    icon: 'notebook'
                },
                {
                    id: 'skills',
                    name: $.fn.efront('translate', 'Skills'),
                    icon: 'chess_piece_rook'
                },
                {
                    id: 'training_sessions',
                    name: $.fn.efront('translate', 'Sessions'),
                    icon: 'dictionary'
                }
            ],
            optgroupField: 'type',
            searchField: ['text', 'type', 'email', 'name', 'surname'],
            optgroupLabelField: 'name',
            optgroupValueField: 'id',
            plugins: ['remove_button'],
            create: false,
            render: {
                optgroup_header: function (data, escape) {
                    return '<div class="opt-group-header"><strong>' + data.name + '</strong><!-- a href=""><span>' + $.fn.efront("translate", "Show all") + '<i class="fa fa-arrow-circle-right"></i></span></a --></div>';
                },
                option: function (item, escape) {
                    $customImg = false;

                    if (typeof item.avatar_data != 'undefined' && item.avatar_data != null) {
                        $customImg = true;
                    }

                    $str = '<table class="opt-group-table"><tr><td>';

                    if (item.type == 'locations') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-earth_location medium"></div>';
                    } else if (item.type == 'jobs') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-notebook medium"></div>';
                    } else if (item.type == 'skills') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-chess_piece_rook medium"></div>';
                    } else if (item.type == 'groups') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-users medium"></div>';
                    } else if (item.type == 'lessons') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:bottom center" class="icon-notebook2 medium"></div>';
                    } else if (item.type == 'training_sessions') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:bottom center" class="icon-notebook2 medium"></div>';
                    } else if (item.type == 'files') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-index medium"></div>';
                    } else if (item.type == 'branches') {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-elements_branch medium"></div>';
                    } else if ($customImg) {
                        $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background:url(' + item.avatar_data + ') no-repeat;background-size:cover"></div>';
                    } else {
                        if (item.type == 'users') {
                            $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;line-height:20px !important;font-size:16px;" class="img-placeholder alphatar search">';
                            $str += '<div style="background-color:' + $.fn.efront('getAlphatarBgColor', item.name, item.surname, item.email) + ';border:none;" class="img-thumbnail" data-sign="' + ($.trim(item.name).charAt(0) + $.trim(item.surname).charAt(0)) + '"></div>';
                            $str += '</div>';
                            //$str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-user medium"></div>';
                        } else if (item.type == 'courses') {
                            $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:bottom center" class="icon-book2 medium"></div>';
                        } else if (item.type == 'curriculums') {
                            $str += '<div style="border-radius:20px;min-height:40px;max-height:40px;height:40px;max-width:40px;width:40px;min-width:40px;margin:5px;background-position:center center" class="icon-dictionary medium"></div>';
                        }
                    }

                    if (item.type == 'curriculums') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666">#<strong>' + item.category_title + '</strong></small></td></tr></table>';
                    } else if (item.type == 'locations') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666"><em>' + ((item.address == null || item.address == '') ? '<span class="text-danger">' + $.fn.efront('translate', 'No address provided') + '</span>' : item.address) + '</em>' + ((item.virtual == 1) ? ' &middot; #<strong>' + $.fn.efront('translate', 'Virtual') + '</strong>' : '') + '</small></td></tr></table>';
                    } else if (item.type == 'jobs') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666"><em>' + ((item.description == null || item.description == '') ? '<span class="text-danger">' + $.fn.efront('translate', 'No description provided') + '</span>' : item.description) + '</em></small></td></tr></table>';
                    } else if (item.type == 'skills') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666">#<strong>' + item.skill_category_name + '</strong></small></td></tr></table>';
                    } else if (item.type == 'users') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666">' + item.email + ' &middot; ' + item.user_type + '</small></td></tr></table>';
                    } else if (item.type == 'groups') {
                        $str += '</td><td style="color:#333">' + item.text + '<br>&ensp;</td></tr></table>';
                    } else if (item.type == 'branches') {
                        $str += '</td><td style="color:#333">' + item.text + '<br>&ensp;</td></tr></table>';
                    } else if (item.type == 'courses') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666">#<strong>' + item.category_title + '</strong>, #<strong>' + $.fn.efront('translate', item.course_type) + '</strong></small></td></tr></table>';
                    } else if (item.type == 'training_sessions') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666">' + $.fn.efront('translate', 'From <b>%s</b> to <b>%s</b>, in <b>%s</b>.', item.start, item.end, item.location_name) + '</small></td></tr></table>';
                    } else if (item.type == 'lessons') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666">#<strong>' + item.course_name + '</strong>, #<strong>' + $.fn.efront('translate', item.lesson_type) + '</strong></small></td></tr></table>';
                    } else if (item.type == 'files') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small><img height="16" width="16" src="' + item.mime_icon + '" /></small></td></tr></table>';
                    } else if (item.type == 'topics') {
                        $str += '</td><td style="color:#333">' + item.text + '<br><small style="font-size:11px;color:#666">' + item.email + '</small></td></tr></table>';
                    }

                    return $str;
                }
            },
            onType: function (str) {
                this.refreshOptions();

                $selectizeDropDownContent = this.$input.next('.ef-autocomplete').find('.selectize-dropdown-content');

                $selectizeDropDownContent.css('min-height', 'auto').css('max-height');

                $height = $($selectizeDropDownContent)[0].scrollHeight;

                if ($height > $(window).height() - 200) {
                    $height = $(window).height() - 200;
                }

                $selectizeDropDownContent.css({
                    'margin': '0',
                    'padding': '5px 0 5px 0',
                    'min-height': $height
                });

                if (str == '') {
                    this.close(); //otherwise, when pressing backspace until the string is deleted, the drop down still appears
                }
            },
            onDropdownClose: function () {
                if (this.$input.hasClass('ef-search-field')) { //special handling of the search field
                    this.clearOptions();
                }
            },
            onItemAdd: function (value, item) {
                if (this.$input.hasClass('ef-search-field')) { //special handling of the search field
                    this.clearOptions();
                    this.blur();
                    window.location = $.fn.efront('url', {
                        'ctg': 'search',
                        'item': value
                    });
                } else if (typeof window.ef_autocomplete_callback != 'undefined') {
                    ef_autocomplete_callback(value);
                }

                $selectizeDropDownContent = this.$input.next('.ef-autocomplete').find('.selectize-dropdown-content');

                $selectizeDropDownContent.css('min-height', 'auto');

                $height = $($selectizeDropDownContent)[0].scrollHeight;

                if ($height > $(window).height() - 200) {
                    $height = $(window).height() - 200;
                }

                $selectizeDropDownContent.css({
                    'margin': '0',
                    'padding': '5px 0 5px 0',
                    'min-height': $height
                });
            },
            onItemRemove: function (value, item) {
                $selectizeDropDownContent = this.$input.next('.ef-autocomplete').find('.selectize-dropdown-content');

                $selectizeDropDownContent.css('min-height', 'auto');

                $height = $($selectizeDropDownContent)[0].scrollHeight;

                if ($height > $(window).height() - 200) {
                    $height = $(window).height() - 200;
                }

                $selectizeDropDownContent.css({
                    'margin': '0',
                    'padding': '5px 0 5px 0',
                    'min-height': $height
                });
            },
            onLoad: function (data) {
                $selectizeDropDownContent = this.$input.next('.ef-autocomplete').find('.selectize-dropdown-content');

                $selectizeDropDownContent.css('min-height', 'auto');

                $height = $($selectizeDropDownContent)[0].scrollHeight;

                if ($height > $(window).height() - 200) {
                    $height = $(window).height() - 200;
                }

                $selectizeDropDownContent.css({
                    'margin': '0',
                    'padding': '5px 0 5px 0',
                    'min-height': $height
                });
            },
            load: function (query, callback) {
                var type = this.$input.data('type');
                var input = this.$input;
                var $this = this;
                var url = $.fn.efront('url', {
                    'ctg': 'list'
                });
                if (this.$input.data('url')) {
                    url = this.$input.data('url');
                }
                if (!query.length) return callback();
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    beforeSend: function () {
                        input.parents(".navbar-form").addClass("loading");
                    },
                    complete: function () {
                        input.parents(".navbar-form").removeClass("loading");

                        if ($(input).data('id') == 'search-text') {
                            $that = input.next('.ef-autocomplete').find('.selectize-dropdown');

                            $that.css({
                                'right': '0',
                                'left': 'auto'
                            });

                            $width = 400;
                            $docWidth = $(document).width();

                            if ($docWidth < $width) {
                                $width = $docWidth;
                            }
                        }
                    },
                    data: {
                        type: type,
                        search: query,
                        limit: 100
                    },
                    error: function () {
                        callback();
                    },
                    success: function (res) {
                        input.next('.ef-autocomplete').find('.selectize-dropdown #no-results').remove();

                        if (res.total == 0) {
                            input.next('.ef-autocomplete').find('.selectize-dropdown').show().append(
                                '<div id="no-results" style="padding:0 5px 5px 5px;text-align:center;font-size:12px">' + $.fn.efront('translate', 'no results found') + '</div>'
                            );
                        }

                        callback(res.data);
                    }
                });
            }
        });
        if ($.fn.efront.onSelectizeReady) {
            $.fn.efront.onSelectizeReady();
        }

    };

    return plugin;
}