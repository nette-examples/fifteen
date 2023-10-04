// general class for handling Nette snippets

class NetteAjax {
	selector = 'a.ajax';
	indicatorId = 'ajax-spinner';
	#indicator = null;
	#mouse = [0, 0];

	initialize() {
		this.#createIndicator();

		window.addEventListener('popstate', (event) => {
			if (event.state.href) {
				this.fetch(event.state.href)
					.then(data => this.processPayload(data));
			}
		});

		document.documentElement.addEventListener('click', (event) => {
			let link = event.target.closest(this.selector);
			if (link) {
				this.#handleClick(event, link);
			}
		});

		window.addEventListener('mousemove', (event) => {
			this.#mouse = [event.pageX, event.pageY];
		}, false);
	}

	fetch(url, options = {}) {
		this.showIndicator(...this.#mouse);
		options = {
			...options,
			headers: {
				'X-Requested-With': 'XMLHttpRequest',
			}
		};
		return fetch(url, options)
			.then(response => response.json())
			.finally(() => {
				this.hideIndicator();
			});
	}

	processPayload(payload, pushUrl) {
		if (payload.redirect) {
			window.location.href = payload.redirect;
			return;
		}

		if (payload.snippets) {
			for (let id in payload.snippets) {
				this.updateSnippet(id, payload.snippets[id]);
			}
		}

		if (pushUrl) {
			history.pushState({href: pushUrl}, '', pushUrl);
		}
	}

	#handleClick(event, link) {
		event.preventDefault();
		let interactionEvent = new CustomEvent('interaction', {
			detail: {
				element: link,
				originalEvent: event,
			},
			cancelable: true,
		});
		document.documentElement.dispatchEvent(interactionEvent);

		if (!interactionEvent.defaultPrevented) {
			this.fetch(link.href)
				.then(data => this.processPayload(data, link.href));
		}
	}

	updateSnippet(id, html) {
		let element = document.getElementById(id);
		if (element) {
			element.innerHTML = html;
		}
	}

	showIndicator(x, y) {
		let styles = (x !== undefined && y !== undefined)
			? {display: 'block', position: 'absolute', left: x + 'px', top: y + 'px'}
			: {display: 'block', position: 'fixed', left: '50%', top: '50%'};
		Object.assign(this.#indicator.style, styles);
	}

	hideIndicator() {
		this.#indicator.style.display = 'none';
	}

	#createIndicator() {
		this.#indicator = document.createElement('div');
		this.#indicator.id = this.indicatorId;
		this.#indicator.style.display = 'none';
		document.body.appendChild(this.#indicator);
	}
}



// custom handler for Fifteen Control
class FifteenGame {
	#ajax = new NetteAjax;
	#moving = false;

	constructor(ajax) {
		this.#ajax = ajax;
	}

	initialize() {
		document.documentElement.addEventListener('interaction', (event) => {
			if (event.detail.element.matches('.fifteen a.ajax')) {
				event.preventDefault();
				this.move(event.detail.element);
			}
		});
	}

	move(link) {
		if (this.#moving) {
			return;
		}

		this.#moving = true;
		let img = link.querySelector('img');
		let fetch = this.#ajax.fetch(link.href);
		let transitionEnd = new Promise(resolve => {
			img.addEventListener('transitionend', () => {
				this.#moving = false;
				resolve();
			}, {once: true});
		});

		let delta = link.getAttribute('rel').split(',');
		img.style.zIndex = 1000;
		img.style.left = delta[0] * img.getAttribute('width') + 'px';
		img.style.top = delta[1] * img.getAttribute('height') + 'px';

		Promise.all([fetch, transitionEnd])
			.then(([data]) => this.#ajax.processPayload(data, link.href));
	}
}


let ajax = new NetteAjax;
ajax.initialize();

let fifteen = new FifteenGame(ajax);
fifteen.initialize();
