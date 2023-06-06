import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLFormElement> {
	static targets = ['url', 'output'];

	declare urlTarget: HTMLInputElement;
	declare outputTarget: HTMLDivElement;

	async shorten(e: Event): Promise<void> {
		e.preventDefault();

		if (!this.urlTarget.value.length) {
			return;
		}

		try {
			const formData = new FormData(this.element);

			const res = await fetch('/', {
				method: 'POST',
				headers: {
					'Accept': 'application/json',
				},
				body: formData
			});

			const result: ShortcodeResult = await res.json();

			const tag = document.createElement('a');
			tag.href = window.location.href + result.shortCode;
			tag.target = '_blank';
			tag.innerHTML = window.location.href + result.shortCode;

			this.outputTarget.innerHTML = `Shortened URL: ${tag.outerHTML}`;
		} catch (e) {
			console.log(e);
		}
	}
}

interface ShortcodeResult {
	shortCode: string
}