/* global p4_data, jQuery, Backbone, _ */

const p4_en = {
  api_url: p4_data.api_url,
  Router: null,
  Models: {
    Question: null,
    Field: null
  },
  Collections: {
    QuestionsCollection: null
  },
  Views: {
    FieldsListView: null,
    FieldsListItemView: null,
    NewFieldView: null
  }
};

// Define custom event on #en_settings_notices element.
$('#en_settings_notices').on('message:add', function (event, options) {
  const $el = $('<div>', {'class': options.type, text: options.message});
  $('#en_settings_notices').append($el);
  setTimeout(function () {
    $el.remove();
  }, 5000);
});

// Set wp nonce header needed for wp rest api authentication.
Backbone.$.ajaxSetup({
  headers: {'X-WP-Nonce': p4_data.nonce}
});


(function ($, p4_en) {
  'use strict';

  p4_en = {
    Router: Backbone.Router.extend({
      routes: {
        'questions': 'showQuestions',
        'questions_available': 'showAvailable',
        'questions/new': 'newQuestion',
        'questions/edit/:id': 'editQuestion'
      }
    }),

    Models: {
      Question: Backbone.Model.extend({
        urlRoot: p4_data.api_url + '/questions',

        defaults: {
          id: 0,
          name: null,
          questionId: 0,
          label: '',
          type: null,
        }
      }),

      QuestionAvailable: Backbone.Model.extend({
        urlRoot: p4_data.api_url + '/questions_available',

        defaults: {
          id: 0,
          name: null,
          questionId: 0,
          label: '',
        }
      }),
    },

    Collections: {
    },
    Views: {
      FieldsListView: null,
      FieldsListItemView: null,
      NewFieldView: null
    }
  };

  /**
   * Collection of questions.
   */
  p4_en.Collections.QuestionsCollection = Backbone.Collection.extend({
    model: p4_en.Models.Question,
    url: p4_data.api_url + '/questions'
  });

  /**
   * Collection of questions.
   */
  p4_en.Collections.AvailableQuestionsCollection = Backbone.Collection.extend({
    model: p4_en.Models.QuestionAvailable,
    url: p4_data.api_url + '/questions_available',

    comparator: function (a) {
      return a.get('type') + a.get('name').toLowerCase();
    }
  });


  /**
   * A view for listing questions.
   */
  p4_en.Views.QuestionsListView = Backbone.View.extend({
    el: '#selected-questions-div',
    template: _.template($('#tmpl-en-questions').html()),

    events: {
      'click .create-question': 'addQuestion',
      'click .edit-question': 'editQuestion',
      'click .reload-questions': 'reload',
      'click .cancel-question': 'reload',
    },

    initialize: function () {
      this.listenTo(this.collection, 'sync', this.render);
      this.listenTo(this.collection, 'remove', function () {
        this.collection.fetch();
      });
      this.listenTo(this.collection, 'add', this.render);
    },

    renderOne: function (question) {
      const questionView = new p4_en.Views.QuestionsListItemView({model: question});
      this.$('.questions-container').append(questionView.render().$el);
    },

    render: function () {
      const html = this.template({col: this.collection});
      this.$el.html(html);
      $('#new-question-div').html('');

      this.$el.find('.questions-container').fadeTo('fast', 0.33);
      this.collection.each(this.renderOne, this);
      this.$el.find('.questions-container').fadeTo('slow', 1);

      return this;
    },

    reload: function (e) {
      e.preventDefault();
      p4_en.refresh_data();
    },

    validateFields: function() {
      const name = $('#en_question_name').val();
      const label = $('#en_question_label').val();
      const type = $('#en_question_type').val();
      const id = $('#en_question_id').val();
      const questionId = $('#en_question__id').val();

      if ('' === name || '' === label) {
        alert('Name and Question can\'t be empty');
        return false;
      }

      return {
        id: id,
        name: name,
        label: label,
        type: type,
        questionId: questionId,
      };
    },

    addQuestion: function (e) {
      e.preventDefault();

      const attrs = this.validateFields();
      if (false === attrs) {
        return;
      }

      const question = new p4_en.Models.Question(attrs);
      p4_en.show_loader();
      question.save({}, {
        type: 'POST',
        url: p4_data.api_url + '/questions',

        success: function () {
          p4_en.add_message('Question has been saved.', 'updated');
          p4_en.hide_loader();
          p4_en.refresh_data();
        },
        error: function (model, xhr) {
          const resp = xhr.responseJSON;
          const messages = resp.messages;
          p4_en.add_message(messages.join('<br>'), 'error');
          p4_en.hide_loader();
        },
      });
    },

    editQuestion: function (e) {
      e.preventDefault();

      const attrs = this.validateFields();
      if (false === attrs) {
        return;
      }

      const question = p4_en.questions.get(attrs.id);
      question.set(attrs);
      p4_en.show_loader();
      question.save({}, {

        success: function () {
          console.log('The model has been saved to the server'); // eslint-disable-line no-console
          p4_en.add_message('Question has been saved.', 'updated');
          p4_en.hide_loader();
          p4_en.refresh_data();
        },
        error: function (model, xhr) {
          console.log('Something went wrong while saving the model'); // eslint-disable-line no-console
          const resp = xhr.responseJSON;
          p4_en.add_message(resp.messages.join('<br>'), 'error');
          p4_en.hide_loader();
        }
      });
    },
  });

  /**
   * A single question view.
   */
  p4_en.Views.QuestionsListItemView = Backbone.View.extend({
    className: 'question-list-item card',
    tagName: 'li',
    template: _.template($('#tmpl-en-question').html()),

    events: {
      'click .edit-this-question': 'edit',
      'click .delete-question': 'delete'
    },

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'destroy', this.remove);
    },

    render: function () {
      const html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    },

    edit: function (e) {
      e.preventDefault();
      const newQuestionForm = new p4_en.Views.NewQuestionView({
        model: this.model,
      });

      let $new_question_div = $('#new-question-div');
      $new_question_div.html(newQuestionForm.render().$el);
      $('html, body').animate({
        scrollTop: $new_question_div.offset().top
      }, 500);
    },

    delete: function (e) {
      e.preventDefault();
      p4_en.show_loader();
      this.model.destroy({
        success: function () {
          p4_en.add_message('Question has been deleted', 'updated');
        },
        error: function (model, xhr) {
          const resp = xhr.responseJSON;
          p4_en.add_message(resp.messages.join('<br>'), 'error');
        },
        wait: true
      }).then(function () {
        p4_en.hide_loader();
        p4_en.refresh_data();
      });
    }
  });

  /**
   * A view for listing questions.
   */
  p4_en.Views.AvailableQuestionsListView = Backbone.View.extend({
    el: '#existing-questions-div',
    template: _.template($('#tmpl-en-available-questions').html()),

    events: {
      'click .reload-questions': 'reload',
    },

    initialize: function () {
      this.listenTo(this.collection, 'sync', this.render);
      this.listenTo(this.collection, 'remove', function () {
        this.collection.fetch();
      });
      this.listenTo(this.collection, 'add', this.render);
    },

    renderOne: function (question) {
      const questionView = new p4_en.Views.AvailableQuestionsListItemView({model: question});
      this.$('.available-questions-container').append(questionView.render().$el);
    },

    render: function () {
      const html = this.template({col: this.collection});
      this.$el.html(html);
      this.$el.find('.available-questions-container').fadeTo('fast', 0.33);
      this.collection.each(this.renderOne, this);
      this.$el.find('.available-questions-container').fadeTo('slow', 1);

      return this;
    },

    reload: function () {
      p4_en.router.navigate('questions_available', true);
    },

  });

  /**
   * A single question view.
   */
  p4_en.Views.AvailableQuestionsListItemView = Backbone.View.extend({
    className: 'question-list-item card',
    tagName: 'li',
    template: _.template($('#tmpl-en-available-question').html()),

    events: {
      'click .add-question': 'add',
    },

    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
    },

    render: function () {
      const html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    },

    add: function (e) {
      e.preventDefault();
      const newQuestionForm = new p4_en.Views.NewQuestionView({
        model: this.model,
      });
      let $new_question_div = $('#new-question-div');
      $new_question_div.html(newQuestionForm.render().$el);
      $('html, body').animate({
        scrollTop: $new_question_div.offset().top
      }, 500);
    },
  });

  /**
   * A new question view.
   */
  p4_en.Views.NewQuestionView = Backbone.View.extend({
    tagName: 'div',
    template: _.template($('#tmpl-new-en-question').html()),


    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
      this.listenTo(this.model, 'destroy', this.remove);
    },

    render: function () {
      const html = this.template(this.model.toJSON());
      this.$el.html(html);
      return this;
    }
  });


  /**
   * Retrieves questions from backend.
   */
  p4_en.refresh_data = function () {
    p4_en.show_loader();
    p4_en.questions.fetch({
      success: function (collection) {
        p4_en.add_message('Questions reloaded', 'updated');
        p4_en.questions_view.collection = collection;
        p4_en.questions_view.render();
      },
      wait: true
    }).then(function () {
      p4_en.hide_loader();
    });
  };

  /**
   * Retrieves questions from backend.
   */
  p4_en.refresh_available = function () {
    p4_en.show_loader();
    p4_en.available_questions.fetch({
      success: function (collection) {
        p4_en.add_message('Available Questions reloaded', 'updated');
        p4_en.available_questions_view.collection = collection;
        p4_en.available_questions_view.render();
      },
      wait: true
    }).then(function () {
      p4_en.hide_loader();
    });
  };

  /**
   * Show loader image
   */
  p4_en.show_loader = function () {
    $('#en_loader').removeClass('hidden');
  };

  /**
   * Hide loader image
   */
  p4_en.hide_loader = function () {
    $('#en_loader').addClass('hidden');
  };

  /**
   * Add a message div to dom.
   * @param message
   * @param type
   */
  p4_en.add_message = function (message, type) {
    $('#en_settings_notices').trigger('message:add', {message: message, type: type});
  };

  /**
   * Initialize data.
   */
  p4_en.init = function () {

    // Create a new router for the app.
    p4_en.router = new p4_en.Router();

    // Instantiate questions collection.
    p4_en.questions = new p4_en.Collections.QuestionsCollection();
    p4_en.available_questions = new p4_en.Collections.AvailableQuestionsCollection();

    p4_en.questions_view = new p4_en.Views.QuestionsListView();
    p4_en.available_questions_view = new p4_en.Views.AvailableQuestionsListView();


    // Define functions for the different routes.
    p4_en.router.on('route:showQuestions', function () {
      p4_en.refresh_data();
    });

    p4_en.router.on('route:showAvailable', function () {
      p4_en.refresh_available();
    });

    p4_en.router.on('route:newQuestion', function () {
      p4_en.new_question_view.model = new p4_en.Models.Question({isNew: true});
      $('#new-question-div').html(p4_en.new_question_view.render().$el);
    });

    p4_en.router.on('route:editQuestion', function (id) {
      if (0 === p4_en.questions.length) {
        p4_en.router.navigate('questions', {
          trigger: true,
          replace: true
        });
        return;
      }
      p4_en.new_question_view.model = p4_en.questions.get(id);
      $('#new-question-div').html(p4_en.new_question_view.render().$el);
    });

    // Instantiate history and navigate to questions.
    Backbone.history.start();
    p4_en.refresh_data();
    p4_en.refresh_available();
  };

  $(document).ready(function () {
    p4_en.init();
  });

})(jQuery, p4_en);
