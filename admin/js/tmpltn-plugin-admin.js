(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 * 
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	$(function() {
		$('#show_sales_web').trigger('click');
		$('#show_sales_square').trigger('click');
		$('#show_stock_total').trigger('click');
		$('#show_sales_total').trigger('click');

		const getCellValue = (tr, idx) => { 
			return tr.children[idx].innerText || tr.children[idx].textContent 
		};
		
		const comparer = (idx, asc) => (a, b) => ((v1, v2) => 
			v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
			)(getCellValue(asc ? a : b, idx), getCellValue(asc ? b : a, idx));
		console.log("testing");
		// do the work...
		document.querySelectorAll('thead tr:nth-child(2) th').forEach(th => { console.log(th);
			th.addEventListener('click', (() => {
			const table = th.closest('table');
			const tbody = table.querySelector('tbody');
			Array.from(tbody.querySelectorAll('tr'))
				.sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
				.forEach(tr => tbody.appendChild(tr) );
		}))
		});

	});

})( jQuery );

