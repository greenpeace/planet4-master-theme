const setupPostsReport = () => {
  let postCollection;
  let pageCollection;
  let campaignCollection;
  let actionCollection;
  let postsView;
  let pagesView;
  let campaignsView;
  let actionsView;

  const p4 = window.p4 || {};
  const p4_data = window.p4_data || {};
  const wp = window.wp || {};

  if ('undefined' === wp.api) {
    return;
  }

  const filterButton = document.querySelector('#posts-filter');
  filterButton.onclick = () => {
    const filters = {};
    const from = document.querySelector('#from').value;
    const to = document.querySelector('#to').value;

    if (from) {
      filters.after = `${from}T00:00:00`;
    }
    if (to) {
      filters.before = `${to}T23:59:59`;
    }
    postsView.refreshPosts(filters);
    pagesView.refreshPages(filters);
    campaignsView.refreshCampaigns(filters);
    actionsView.refreshActions(filters);
  };

  const hideSpinner = postType => document.querySelector(`#${postType}_loader`).classList.add('hidden');
  const showSpinner = postType => document.querySelector(`#${postType}_loader`).classList.remove('hidden');

  // Posts List
  p4.PostsView = wp.Backbone.View.extend({
    template: wp.template('p4-post-list'),
    events: {
      'click .refresh'() {
        return this.refreshPosts({});
      },
    },
    showSpinner: () => showSpinner('posts'),
    hideSpinner: () => hideSpinner('posts'),
    refreshPosts(filters) {
      this.showSpinner();
      const params = {
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
        headers: {'X-WP-Nonce': p4_data.nonce},
        success: this.hideSpinner,
        error: this.hideSpinner,
      });
    },
    initialize() {
      this.listenTo(this.collection, 'add', this.addPostView);
    },
    addPostView(post) {
      this.views.add('.p4-posts', new p4.PostView({model: post}));
    },
  });

  p4.PostView = wp.Backbone.View.extend({
    template: wp.template('p4-post'),
    tagName: 'tr',
    prepare() {
      return this.model.toJSON();
    },
  });

  // Pages list
  p4.PagesView = wp.Backbone.View.extend({
    template: wp.template('p4-page-list'),
    events: {
      'click .refresh'() {
        return this.refreshPages({});
      },
    },
    showSpinner: () => showSpinner('pages'),
    hideSpinner: () => hideSpinner('pages'),
    refreshPages(filters) {
      this.showSpinner();
      const params = {
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
        headers: {'X-WP-Nonce': p4_data.nonce},
        success: this.hideSpinner,
        error: this.hideSpinner,
      });
    },
    initialize() {
      this.listenTo(this.collection, 'add', this.addPageView);
    },
    addPageView(post) {
      this.views.add('.p4-pages', new p4.PostView({model: post}));
    },
  });

  p4.PageView = wp.Backbone.View.extend({
    template: wp.template('p4-post'),
    tagName: 'tr',
    prepare() {
      return this.model.toJSON();
    },
  });

  // Campaigns list
  p4.CampaignsView = wp.Backbone.View.extend({
    template: wp.template('p4-campaign-list'),
    events: {
      'click .refresh'() {
        return this.refreshCampaigns({});
      },
    },
    showSpinner: () => showSpinner('campaigns'),
    hideSpinner: () => hideSpinner('campaigns'),
    refreshCampaigns(filters) {
      this.showSpinner();
      const params = {
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
        url: p4_data.api_url + '/campaign',
        data: params,
        headers: {'X-WP-Nonce': p4_data.nonce},
        success: this.hideSpinner,
        error: this.hideSpinner,
      });
    },
    initialize() {
      this.listenTo(this.collection, 'add', this.addCampaignView);
    },
    addCampaignView(post) {
      this.views.add('.p4-campaigns', new p4.PostView({model: post}));
    },
  });

  p4.CampaignView = wp.Backbone.View.extend({
    template: wp.template('p4-post'),
    tagName: 'tr',
    prepare() {
      return this.model.toJSON();
    },
  });

  // Actions list
  p4.ActionsView = wp.Backbone.View.extend({
    template: wp.template('p4-action-list'),
    events: {
      'click .refresh'() {
        return this.refreshActions({});
      },
    },
    showSpinner: () => showSpinner('actions'),
    hideSpinner: () => hideSpinner('actions'),
    refreshActions(filters) {
      this.showSpinner();
      const params = {
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
        url: p4_data.api_url + '/p4_action',
        data: params,
        headers: {'X-WP-Nonce': p4_data.nonce},
        success: this.hideSpinner,
        error: this.hideSpinner,
      });
    },
    initialize() {
      this.listenTo(this.collection, 'add', this.addActionView);
    },
    addActionView(post) {
      this.views.add('.p4-actions', new p4.PostView({model: post}));
    },
  });

  p4.ActionView = wp.Backbone.View.extend({
    template: wp.template('p4-post'),
    tagName: 'tr',
    prepare() {
      return this.model.toJSON();
    },
  });

  p4.initialize = () => {
    postCollection = new wp.api.collections.Posts();
    pageCollection = new wp.api.collections.Pages();
    campaignCollection = new wp.api.collections.Campaign();
    actionCollection = new wp.api.collections.P4_action();
    postsView = new p4.PostsView({collection: postCollection});
    pagesView = new p4.PagesView({collection: pageCollection});
    campaignsView = new p4.CampaignsView({collection: campaignCollection});
    actionsView = new p4.ActionsView({collection: actionCollection});
    postCollection.fetch();
    pageCollection.fetch();
    campaignCollection.fetch();
    actionCollection.fetch();

    document.querySelector('#posts-table').appendChild(postsView.render().el);
    document.querySelector('#pages-table').appendChild(pagesView.render().el);
    document.querySelector('#campaigns-table').appendChild(campaignsView.render().el);
    const actionsTable = document.querySelector('#actions-table');
    if (actionsTable) {
      actionsTable.appendChild(actionsView.render().el);
    }
  };

  // Initialize page when wp api client has finished loading.
  wp.api.loadPromise.done(p4.initialize);
};

setupPostsReport();
