/**
 * @author    Supercool Ltd <josh@supercooldesign.co.uk>
 * @copyright Copyright (c) 2014, Supercool Ltd
 * @see       http://supercooldesign.co.uk
 */

(function($){


/**
 * Fetch Class
 */
Craft.Fetch = Garnish.Base.extend(
{

  id: null,
  refreshTimout: null,

  $elem: null,
  $input: null,
  $spinner: null,
  $error: null,
  $success: null,
  $preview: null,

  init: function(id)
  {

    this.id = id;
    this.$elem = $('#'+this.id);
    this.$input = this.$elem.find('input');
    this.$spinner = $('<div class="spinner hidden" />').appendTo(this.$elem);
    this.$success = $('<div class="success hidden" data-icon="check" />').appendTo(this.$elem);
    this.$error = $('<div class="error hidden" data-icon="alert" />').appendTo(this.$elem);
    this.$errorContainer = $('<div class="fetch-errors hidden" />').appendTo(this.$elem);
    this.$preview = $('<div class="fetch-preview hidden" />').appendTo(this.$elem);

    this.addListener(this.$input, 'keyup', 'refreshPreview');
    this.$input.trigger('keyup');

  },

  refreshPreview: function()
  {

    if (this.refreshTimout)
    {
      clearTimeout(this.refreshTimout);
    }

    this.refreshTimout = setTimeout($.proxy(function()
    {
      this.getPreview();
    }, this), 1000);

  },

  getPreview: function()
  {

    if ( this.$input.val() !== '' )
    {

      this.setWorking();

      var data = {
        url : this.$input.val()
      };

      Craft.postActionRequest('fetch/get', data, $.proxy(function(data, textStatus, jqXHR)
      {
        if ( data.success )
        {
          this.setSuccess(data.html);
        }
        else
        {
          this.setError(data.error);
        }
      }, this));

    }

  },

  setWorking: function()
  {
    // set icons
    this.$spinner.removeClass('hidden');
    this.$error.addClass('hidden');
    this.$success.addClass('hidden');
  },

  setSuccess: function(html)
  {

    // set icons
    this.$spinner.addClass('hidden');
    this.$error.addClass('hidden');
    this.$success.removeClass('hidden');

    // clear errors and show html
    this.$errorContainer.addClass('hidden').html('');
    this.$preview.html(html).removeClass('hidden');

  },

  setError: function(error)
  {

    // set icons
    this.$spinner.addClass('hidden');
    this.$error.removeClass('hidden');
    this.$success.addClass('hidden');

    // clear native errors and show our error, even though they are likely
    // to be the same error it means we can keep ours up-to-date
    this.$elem.parent('.input').removeClass('errors').next('.errors').remove();
    this.$errorContainer.html('<p class="error">'+error+'</p>').removeClass('hidden');
    this.$preview.addClass('hidden').html('');

  }


});


})(jQuery);
