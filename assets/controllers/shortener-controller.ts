import { Controller } from "@hotwired/stimulus";

export default class extends Controller<HTMLDivElement> {
	static targets = ['url', 'output'];

	declare urlTarget: HTMLInputElement;
	declare outputTarget: HTMLDivElement;

	async shorten(): Promise<void> {
		try {
			const res = await fetch('/', {
				method: 'POST',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: `url=${encodeURIComponent(this.urlTarget.value)}`
			});

			console.log(res);

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