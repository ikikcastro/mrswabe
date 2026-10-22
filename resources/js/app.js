import './bootstrap';

const adminMenuToggle = document.getElementById('adminMenuToggle');
const adminSidebar = document.getElementById('adminSidebar');
const adminSidebarClose = document.getElementById('adminSidebarClose');

adminMenuToggle?.addEventListener('click', () => {
	const isOpen = adminSidebar?.classList.toggle('is-open') ?? false;
	adminMenuToggle.setAttribute('aria-expanded', String(isOpen));
});

adminSidebarClose?.addEventListener('click', () => {
	adminSidebar?.classList.remove('is-open');
	adminMenuToggle?.setAttribute('aria-expanded', 'false');
});

const dashboardTabs = document.querySelectorAll('[data-dashboard-tab]');
const dashboardPanels = document.querySelectorAll('[data-dashboard-panel]');

function activateDashboardTab(tabName, updateUrl = true) {
	const activeTab = document.querySelector(`[data-dashboard-tab="${tabName}"]`);

	if (!activeTab) {
		return;
	}

	dashboardTabs.forEach((tab) => {
		const isActive = tab === activeTab;
		tab.classList.toggle('is-active', isActive);
		tab.setAttribute('aria-current', isActive ? 'page' : 'false');
	});
	dashboardPanels.forEach((panel) => {
		const isActive = panel.dataset.dashboardPanel === tabName;
		panel.classList.toggle('is-active', isActive);
		panel.hidden = !isActive;
	});
	adminSidebar?.classList.remove('is-open');
	adminMenuToggle?.setAttribute('aria-expanded', 'false');

	if (updateUrl) {
		history.replaceState(null, '', `#${tabName}`);
	}
}

if (dashboardTabs.length && dashboardPanels.length) {
	activateDashboardTab(window.location.hash.slice(1) || 'overview', false);

	dashboardTabs.forEach((tab) => {
		tab.addEventListener('click', (event) => {
			event.preventDefault();
			activateDashboardTab(tab.dataset.dashboardTab);
		});
	});
}

document.querySelectorAll('[data-payment-action]').forEach((form) => {
	form.addEventListener('submit', async (event) => {
		event.preventDefault();

		const row = form.closest('.booking-row');
		const submitButton = event.submitter ?? form.querySelector('button[type="submit"]');
		const originalLabel = submitButton?.textContent;
		if (submitButton) {
			submitButton.disabled = true;
			submitButton.textContent = 'Saving...';
		}

		try {
			const response = await fetch(form.action, {
				method: 'POST',
				body: new FormData(form),
				headers: {
					Accept: 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
			});
			const result = await response.json().catch(() => ({}));
			if (!response.ok) {
				throw new Error(result.message ?? 'Unable to update this payment.');
			}

			const paymentBadge = row?.querySelector('.payment-status-badge');
			paymentBadge?.classList.remove('payment-status-badge--pending', 'payment-status-badge--unpaid');
			paymentBadge?.classList.add('payment-status-badge--paid');
			if (paymentBadge) paymentBadge.textContent = 'Paid';

			if (result.booking_status) {
				const bookingStatus = row?.querySelector('.booking-status');
				bookingStatus?.classList.remove('booking-status--pending');
				bookingStatus?.classList.add(`booking-status--${result.booking_status}`);
				if (bookingStatus) bookingStatus.textContent = result.booking_status.replaceAll('_', ' ');
			}

			const method = new FormData(form).get('method');
			const paymentMethod = row?.querySelector('.booking-payment');
			if (method && paymentMethod) paymentMethod.textContent = method.toUpperCase();
			(form.closest('.approval-actions') ?? form).remove();
		} catch (error) {
			let feedback = row?.querySelector('.admin-action-feedback');
			if (!feedback && row) {
				feedback = document.createElement('span');
				feedback.className = 'admin-action-feedback';
				feedback.setAttribute('role', 'alert');
				row.querySelector('.booking-meta')?.append(feedback);
			}
			if (feedback) feedback.textContent = error.message;

			if (submitButton?.isConnected) {
				submitButton.disabled = false;
				submitButton.textContent = originalLabel;
			}
		}
	});
});
