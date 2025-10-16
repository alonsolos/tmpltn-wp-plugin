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
		try { console.info('[PDFGen] admin JS loaded'); } catch (e) {}
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

		// Helper: load image URL into dataURL and return original size
		async function toDataURLWithSize(url) {
			return new Promise((resolve, reject) => {
				try {
					const img = new Image();
					img.crossOrigin = 'Anonymous';
					img.onload = function () {
						try {
							const canvas = document.createElement('canvas');
							canvas.width = img.naturalWidth;
							canvas.height = img.naturalHeight;
							const ctx = canvas.getContext('2d');
							ctx.drawImage(img, 0, 0);
							const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
							resolve({ dataUrl, width: img.naturalWidth, height: img.naturalHeight });
						} catch (e) {
							reject(e);
						}
					};
					img.onerror = () => reject(new Error('Image load error'));
					// cache buster to avoid cached CORS issues
					img.src = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'nocache=' + Date.now();
				} catch (err) {
					reject(err);
				}
			});
		}

		// PDF Generator: export selected category
		async function tmpltnExportPdfHandler(e) {
			try {
				try { console.info('[PDFGen] click export'); } catch (e) {}
				if (typeof tmpltnPdfGen !== 'object' || !tmpltnPdfGen.ajaxUrl) {
					alert('Configuración no cargada (tmpltnPdfGen). Recarga la página.');
					return;
				}
				if (!window.jspdf || !window.jspdf.jsPDF) {
					alert('Librería PDF no cargada. Verifica conexión al CDN.');
					return;
				}
				const $btn = $(this);
				const catId = $('input[name="product_cat"]:checked').val();
				if (!catId) {
					alert('Selecciona una categoría primero.');
					return;
				}
				// Derive category name from label and precompute filename base
				const $selected = $('input[name="product_cat"]:checked');
				const catLabel = ($selected.closest('label').text() || '').trim();
				function sanitizeForFilename(s) {
					try {
						let name = s || '';
						if (name.normalize) {
							name = name.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
						}
						name = name.replace(/[^A-Za-z0-9\\s\\-_]+/g, '');
						name = name.trim().replace(/\\s+/g, '-');
						if (!name) name = 'categoria';
						return name;
					} catch (e) {
						return 'categoria';
					}
				}
				const filenameBase = sanitizeForFilename(catLabel);

				$btn.prop('disabled', true).text('Generando...');

				// Fetch visible products for category
				const res = await $.ajax({
					url: tmpltnPdfGen.ajaxUrl,
					method: 'POST',
					dataType: 'json',
					data: {
						action: 'tmpltn_get_products_by_cat',
						_ajax_nonce: tmpltnPdfGen.nonce,
						cat_id: catId
					}
				});

				if (!res || !res.success) {
					alert('No se pudo obtener los productos.');
					return;
				}
				const items = res.data || [];
				if (!items.length) {
					alert('No hay productos visibles en esta categoría.');
					return;
				}

				// Build PDF
				const { jsPDF } = window.jspdf;
				const doc = new jsPDF({ unit: 'mm', format: 'a4' });
				const margin = 15;
				const pageWidth = doc.internal.pageSize.getWidth();
				const innerWidth = pageWidth - margin * 2;

				for (let i = 0; i < items.length; i++) {
					const item = items[i];
					if (i > 0) doc.addPage();

					// Title
					doc.setFontSize(16);
					// Decode any HTML entities just in case
					const titleText = (function (s) {
						try {
							const t = document.createElement('textarea');
							t.innerHTML = String(s == null ? '' : s);
							return t.value;
						} catch (e) {
							return String(s || '');
						}
					})(item.name);
					doc.text(titleText, margin, margin);

					// Subtitle with available sizes (variations with stock > 0)
					let imageStartY = margin + 8;
					if (Array.isArray(item.sizes) && item.sizes.length) {
						doc.setFontSize(12);
						const sizesLine = 'Disponible en: ' + item.sizes.join(', ');
						doc.text(sizesLine, margin, margin + 6);
						imageStartY = margin + 14;
					}

					// Image (optional)
					if (item.image) {
						try {
							const meta = await toDataURLWithSize(item.image);
							// Scale image to fit width
							const scale = innerWidth / (meta.width || innerWidth);
							const drawW = innerWidth;
							const drawH = (meta.height || innerWidth) * scale;
							const y = imageStartY;
							doc.addImage(meta.dataUrl, 'JPEG', margin, y, drawW, drawH, undefined, 'FAST');
						} catch (e) {
							// Ignore image errors, continue with text only
							console.warn('No se pudo cargar la imagen para PDF:', e);
						}
					}
				}

				doc.save('catalogo ' + filenameBase + '.pdf');
			} catch (e) {
				console.error(e);
				alert('Ocurrió un error al generar el PDF.');
			} finally {
				$('#tmpltn-export-pdf').prop('disabled', false).text('Exportar PDF');
			}
		}
		// Delegated binding (works even if the button is injected later)
		$(document).off('click.tmpltn', '#tmpltn-export-pdf').on('click.tmpltn', '#tmpltn-export-pdf', tmpltnExportPdfHandler);
		// Direct binding in case the button already exists at load time
		if ($('#tmpltn-export-pdf').length) {
			$('#tmpltn-export-pdf').off('click.tmpltn').on('click.tmpltn', tmpltnExportPdfHandler);
		}
	});

})( jQuery );
