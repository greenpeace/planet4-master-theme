var postCollection, pageCollection;
var postsView, pagesView;

$ = jQuery;

$(document).ready(function () {
    var from = jQuery("#from")
        .datepicker({
            dateFormat: 'yy-mm-dd',
            defaultDate: '-3w',
            changeMonth: true,
            numberOfMonths: 2
        });
    var to = jQuery('#to').datepicker({
        dateFormat: 'yy-mm-dd',
        defaultDate: '-1w',
        changeMonth: true,
        numberOfMonths: 2
    });

    $('#posts-filter').off('click').on('click', function () {

        var filters = {};
        var from = $('#from').datepicker().val();
        var to = $('#to').datepicker().val();
        if ('' !== from) {
            filters.after = from + 'T00:00:00';
        }
        if ('' !== to) {
            filters.before = to + 'T00:00:00';
        }

        postsView.refreshPosts(filters);
        pagesView.refreshPages(filters);
    });

});


var p4 = p4 || {};
var wp = window.wp || {};

(function ($) {


    p4.PostsView = wp.Backbone.View.extend({

        template: wp.template('p4-post-list'),

        events: {
            'click .refresh': function () {
                return this.refreshPosts({})
            },
        },

        showSpinner: function () {
            $('#posts_loader').removeClass('hidden');
        },

        hideSpinner: function () {
            $('#posts_loader').addClass('hidden');
        },

        refreshPosts: function (filters) {
            this.showSpinner();
            var params = {
                per_page: 50,
                status: 'publish',
                orderby: 'modified',
                order: 'desc',
                date_query_column: 'post_modified',
            };
            Object.assign(params, filters);
            this.collection.reset();
            this.views.remove();
            this.render();
            this.collection.fetch({
                url: p4_data.api_url + '/posts',
                data: params,
                headers: {'X-WP-Nonce': p4_data.nonce}
            });
            this.hideSpinner();
        },

        initialize: function () {
            this.listenTo(this.collection, 'add', this.addPostView);
        },

        addPostView: function (post) {
            this.views.add('.p4-posts', new p4.PostView({model: post}));
        }
    });

    p4.PostView = wp.Backbone.View.extend({
        template: wp.template('p4-post'),
        tagName: 'tr',

        prepare: function () {
            return this.model.toJSON();
        }
    });


    p4.PagesView = wp.Backbone.View.extend({
        template: wp.template('p4-page-list'),

        events: {
            'click .refresh': function () {
                return this.refreshPages({})
            },
        },

        showSpinner: function () {
            $('#pages_loader').removeClass('hidden');
        },

        hideSpinner: function () {
            $('#pages_loader').addClass('hidden');
        },

        refreshPages: function (filters) {
            this.showSpinner();
            var params = {
                per_page: 50,
                status: 'publish',
                orderby: 'modified',
                order: 'desc',
                date_query_column: 'post_modified',
            };
            Object.assign(params, filters);
            this.collection.reset();
            this.views.remove();
            this.render();
            this.collection.fetch({
                url: p4_data.api_url + '/pages',
                data: params,
                headers: {'X-WP-Nonce': p4_data.nonce}
            });
            this.hideSpinner();
        },

        initialize: function () {
            this.listenTo(this.collection, 'add', this.addPostView);
        },

        addPostView: function (post) {
            this.views.add('.p4-pages', new p4.PageView({model: post}));
        }
    });

    p4.PageView = wp.Backbone.View.extend({
        template: wp.template('p4-post'),
        tagName: 'tr',

        prepare: function () {
            return this.model.toJSON();
        }
    });

    p4.initialize = function () {
        postCollection = new wp.api.collections.Posts();
        pageCollection = new wp.api.collections.Pages();
        postsView = new p4.PostsView({collection: postCollection});
        pagesView = new p4.PagesView({collection: pageCollection});
        postCollection.fetch();
        pageCollection.fetch();
        $('#posts-table').html(postsView.render().el);
        $('#pages-table').html(pagesView.render().el);
    };

    $(document).ready(function () {
        p4.initialize();
    });
})(jQuery);