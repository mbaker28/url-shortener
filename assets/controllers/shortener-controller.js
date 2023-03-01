import { Controller } from "@hotwired/stimulus";
import $ from 'jquery';

export default class extends Controller {
	static targets = ['url', 'output'];

	shorten() {
		const data = {
			url: this.urlTarget.value
		};

		this.outputTarget.innerHTML = '';

		$.post('/', data).then((res) => {
			if (typeof res.error !== 'undefined') {
				$(this.outputTarget).html(res.error);
				return;
			}

			const tag = $('<a>')
				.attr('href', window.location.href + res.shortCode)
				.attr('target', '_blank')
				.html(window.location.href + res.shortCode);

			$(this.outputTarget).html(
				`Shortened URL: ${tag[0].outerHTML}`
			);
		});
	}
}