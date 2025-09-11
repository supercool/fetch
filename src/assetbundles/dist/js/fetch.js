// noinspection JSVoidFunctionReturnValueUsed

(function($) {
	Craft.Fetch = Garnish.Base.extend({
		id: null,
		refreshTimeout: null,
		$elem: null,
		$input: null,
		$spinner: null,
		$error: null,
		$success: null,
		$preview: null,

		init: function (id) {
			this.id = id;
			this.$elem = $('#' + this.id);
			this.$input = this.$elem.find('input');
			this.$spinner = $('<div class="spinner hidden" />').appendTo(this.$elem);
			this.$success = $('<div class="success hidden" data-icon="check" />').appendTo(this.$elem);
			this.$error = $('<div class="error hidden" data-icon="alert" />').appendTo(this.$elem);
			this.$preview = $('<div class="fetch-preview hidden" />').appendTo(this.$elem);

			this.refreshPreview();
			this.addListener(this.$input, 'input', 'refreshPreview');
		},

		refreshPreview: function () {
			if (this.refreshTimeout) {
				clearTimeout(this.refreshTimeout);
			}

			this.refreshTimeout = setTimeout(() => this.getPreview(), 500);
		},

		getPreview: function () {
			if (this.$input.val() === '') {
				this.$preview.addClass('hidden').html('');
			} else {
				this.setWorking();

				let data = {
					url: this.$input.val()
				};

				Craft.sendActionRequest('POST', 'fetch/fetch/field-preview', { data })
					.then(response => {
						if (response.data.html) {
							this.setSuccess(response.data);
						} else {
							this.setError('An error occurred when fetching this content.');
						}
					})
					.catch(error => {
						this.setError(error?.response?.data?.message ?? 'An error occurred when fetching this content.');
					})
			}
		},

		setWorking: function() {
			this.$error.addClass('hidden');
			this.$success.addClass('hidden');
			this.$spinner.removeClass('hidden');
		},

		setSuccess: function(data) {
			this.$error.addClass('hidden');
			this.$spinner.addClass('hidden');
			this.$success.removeClass('hidden');
			this.$preview.html(data.html).css({ '--width': data.width, '--height': data.height }).removeClass('hidden');
		},

		setError: function() {
			this.$spinner.addClass('hidden');
			this.$error.removeClass('hidden');
			this.$success.addClass('hidden');
			this.$preview.addClass('hidden').html('');
		},
	});
})(jQuery);
